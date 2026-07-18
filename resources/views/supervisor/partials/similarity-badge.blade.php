@php
    $status = $idea->similarity_status;
    $badgeClass = match ($status) {
        'matched' => ($idea->similarity_level === 'high')
            ? 'similarity-badge similarity-badge--high'
            : 'similarity-badge similarity-badge--moderate',
        'no_match' => 'similarity-badge similarity-badge--none',
        'unavailable' => 'similarity-badge similarity-badge--unavailable',
        default => 'similarity-badge similarity-badge--legacy',
    };
@endphp
<span
    class="{{ $badgeClass }}"
    title="Advisory semantic similarity; not plagiarism detection."
>
    {{ $idea->similarityDisplayLabel() }}
</span>
