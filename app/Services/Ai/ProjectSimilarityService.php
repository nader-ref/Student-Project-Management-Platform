<?php

namespace App\Services\Ai;

use App\Models\idea;
use App\Models\UniProject;
use Illuminate\Support\Facades\Log;

class ProjectSimilarityService
{
    public const DISCLAIMER = 'Similarity is advisory only and is not plagiarism detection.';

    public const MESSAGE_UNAVAILABLE = 'Similarity checking is currently unavailable. You can still submit your idea.';

    public const MESSAGE_NO_MATCHES = 'No significant similarity was found.';

    public const MESSAGE_MATCHES = 'Similar existing projects or accepted ideas were found. Review them and refine your idea if needed.';

    public const QUERY_PREFIX = 'search_query: ';

    public const DOCUMENT_PREFIX = 'search_document: ';

    private const MAX_TEXT_CHARS = 1200;

    private const MAX_DESCRIPTION_CHARS = 220;

    private const BATCH_SIZE = 32;

    public function __construct(
        private readonly OllamaEmbeddingService $embeddings,
    ) {}

    /**
     * Compare a draft proposal against existing projects and accepted ideas.
     *
     * @return array{
     *     ok: bool,
     *     mode: string,
     *     disclaimer?: string,
     *     matches: list<array{
     *         source_type: string,
     *         source_id: int,
     *         title: string,
     *         score: float,
     *         percentage: float,
     *         level: string
     *     }>,
     *     message: string
     * }
     */
    public function compare(string $title, ?string $proposalDescription = null): array
    {
        $queryBody = $this->buildComparisonText($title, $proposalDescription);

        if ($queryBody === null) {
            return $this->unavailable('empty_query');
        }

        if (! $this->embeddings->shouldUseAi()) {
            return $this->unavailable('disabled_or_unsupported_provider');
        }

        $corpus = $this->loadCorpus();

        if ($corpus === []) {
            return $this->semantic([], self::MESSAGE_NO_MATCHES);
        }

        $texts = array_merge(
            [self::QUERY_PREFIX.$queryBody],
            array_map(
                fn (array $row): string => self::DOCUMENT_PREFIX.$row['text'],
                $corpus,
            ),
        );

        $allEmbeddings = $this->embedBatched($texts);

        if ($allEmbeddings === null) {
            return $this->unavailable('embedding_failed');
        }

        $queryEmbedding = $allEmbeddings[0];
        $matches = [];
        $minScore = (float) config('ai.similarity_min_score', 0.66);

        foreach ($corpus as $index => $row) {
            $score = CosineSimilarity::score($queryEmbedding, $allEmbeddings[$index + 1]);

            if ($score === null || $score < $minScore) {
                continue;
            }

            $matches[] = [
                'source_type' => $row['source_type'],
                'source_id' => $row['source_id'],
                'title' => $row['title'],
                'score' => round($score, 3),
                'percentage' => round($score * 100, 1),
                'level' => $this->levelForScore($score),
            ];
        }

        usort($matches, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $topN = max(1, (int) config('ai.similarity_top_n', 5));
        $matches = array_slice($matches, 0, $topN);

        $message = $matches === [] ? self::MESSAGE_NO_MATCHES : self::MESSAGE_MATCHES;

        return $this->semantic($matches, $message);
    }

    /**
     * @return list<array{source_type: string, source_id: int, title: string, text: string}>
     */
    private function loadCorpus(): array
    {
        $corpus = [];

        foreach (UniProject::query()->orderBy('id')->get(['id', 'name', 'description']) as $project) {
            $text = $this->buildComparisonText((string) $project->name, $project->description);
            if ($text === null) {
                continue;
            }

            $corpus[] = [
                'source_type' => 'existing_project',
                'source_id' => (int) $project->id,
                'title' => (string) $project->name,
                'text' => $text,
            ];
        }

        foreach (
            idea::query()
                ->where('accepted', true)
                ->where('rejected', false)
                ->orderBy('id')
                ->get(['id', 'projectname', 'proposal_description']) as $acceptedIdea
        ) {
            $text = $this->buildComparisonText(
                (string) $acceptedIdea->projectname,
                $acceptedIdea->proposal_description,
            );
            if ($text === null) {
                continue;
            }

            $corpus[] = [
                'source_type' => 'accepted_idea',
                'source_id' => (int) $acceptedIdea->id,
                'title' => (string) $acceptedIdea->projectname,
                'text' => $text,
            ];
        }

        return $corpus;
    }

    /**
     * Concise title + short semantic description (representation C).
     * Prefer problem statement over full proposal boilerplate.
     */
    private function buildComparisonText(string $title, ?string $description): ?string
    {
        $title = $this->normalizeWhitespace($title);
        $shortDescription = $this->extractConciseDescription((string) $description);

        if ($title === '' && $shortDescription === '') {
            return null;
        }

        $parts = [];
        if ($title !== '') {
            $parts[] = 'Title: '.$title;
        }
        if ($shortDescription !== '') {
            $parts[] = 'Description: '.$shortDescription;
        }

        $text = implode("\n", $parts);

        if (mb_strlen($text) > self::MAX_TEXT_CHARS) {
            $text = mb_substr($text, 0, self::MAX_TEXT_CHARS);
        }

        return $text;
    }

    /**
     * Keep discriminative topic text; avoid full FR lists and section-heading noise.
     */
    private function extractConciseDescription(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/Problem Statement\s*(.*?)(?=\n\s*Objectives\b|\n\s*Scope\b|\n\s*Initial Functional|\z)/is', $raw, $matches)) {
            $problem = $this->normalizeWhitespace($matches[1]);
            if ($problem !== '') {
                return $this->firstSentenceOrLimit($problem);
            }
        }

        // Strip common AI proposal section headings, then take a short lead.
        $stripped = preg_replace(
            '/^\s*(Problem Statement|Objectives|Scope|Initial Functional Requirements)\s*$/imu',
            '',
            $raw,
        ) ?? $raw;
        $stripped = preg_replace('/^\s*[•\-]\s*/mu', '', $stripped) ?? $stripped;
        $stripped = $this->normalizeWhitespace($stripped);

        return $this->firstSentenceOrLimit($stripped);
    }

    private function firstSentenceOrLimit(string $text): string
    {
        $text = $this->normalizeWhitespace($text);
        if ($text === '') {
            return '';
        }

        if (preg_match('/^(.{20,'.self::MAX_DESCRIPTION_CHARS.'}?[.!?])(\s|$)/u', $text, $matches)) {
            return $this->normalizeWhitespace($matches[1]);
        }

        return mb_substr($text, 0, self::MAX_DESCRIPTION_CHARS);
    }

    private function normalizeWhitespace(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    /**
     * @param  list<string>  $texts
     * @return list<array<int, float>>|null
     */
    private function embedBatched(array $texts): ?array
    {
        $combined = [];

        foreach (array_chunk($texts, self::BATCH_SIZE) as $chunk) {
            $embeddings = $this->embeddings->embed($chunk);
            if ($embeddings === null) {
                return null;
            }

            foreach ($embeddings as $vector) {
                $combined[] = $vector;
            }
        }

        if (count($combined) !== count($texts)) {
            Log::warning('AI similarity embedding batch size mismatch', [
                'expected' => count($texts),
                'actual' => count($combined),
            ]);

            return null;
        }

        return $combined;
    }

    private function levelForScore(float $score): string
    {
        $high = (float) config('ai.similarity_high_score', 0.78);
        $min = (float) config('ai.similarity_min_score', 0.66);

        if ($score >= $high) {
            return 'high';
        }

        if ($score >= $min) {
            return 'moderate';
        }

        return 'low';
    }

    /**
     * @param  list<array{source_type: string, source_id: int, title: string, score: float, percentage: float, level: string}>  $matches
     * @return array{ok: bool, mode: string, disclaimer: string, matches: list<array{source_type: string, source_id: int, title: string, score: float, percentage: float, level: string}>, message: string}
     */
    private function semantic(array $matches, string $message): array
    {
        return [
            'ok' => true,
            'mode' => 'semantic',
            'disclaimer' => self::DISCLAIMER,
            'matches' => array_values($matches),
            'message' => $message,
        ];
    }

    /**
     * @return array{ok: bool, mode: string, matches: list<never>, message: string}
     */
    private function unavailable(string $reason): array
    {
        Log::info('AI similarity unavailable', [
            'reason' => $reason,
        ]);

        return [
            'ok' => false,
            'mode' => 'unavailable',
            'matches' => [],
            'message' => self::MESSAGE_UNAVAILABLE,
        ];
    }
}
