<?php

/**
 * One-off calibration harness for PM-025 similarity discrimination.
 * Run: php scripts/calibrate_similarity.php
 */

require __DIR__.'/../vendor/autoload.php';

$baseUrl = 'http://127.0.0.1:11434';

function normalizeWs(string $value): string
{
    return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
}

function extractSections(string $proposal): array
{
    $sections = [
        'problem' => '',
        'objectives' => '',
        'scope' => '',
        'requirements' => '',
    ];

    if (preg_match('/Problem Statement\s*(.*?)(?=Objectives|$)/is', $proposal, $m)) {
        $sections['problem'] = normalizeWs($m[1]);
    }
    if (preg_match('/Objectives\s*(.*?)(?=Scope|Initial Functional|$)/is', $proposal, $m)) {
        $sections['objectives'] = normalizeWs(preg_replace('/^•\s*/m', '', $m[1]) ?? $m[1]);
    }
    if (preg_match('/Scope\s*(.*?)(?=Initial Functional|$)/is', $proposal, $m)) {
        $sections['scope'] = normalizeWs(preg_replace('/^•\s*/m', '', $m[1]) ?? $m[1]);
    }
    if (preg_match('/Initial Functional Requirements\s*(.*)$/is', $proposal, $m)) {
        $sections['requirements'] = normalizeWs(preg_replace('/^•\s*/m', '', $m[1]) ?? $m[1]);
    }

    return $sections;
}

function shortDescription(string $title, string $proposal): string
{
    $s = extractSections($proposal);
    if ($s['problem'] !== '') {
        // First sentence-ish of problem statement.
        $problem = $s['problem'];
        if (preg_match('/^(.{20,180}?[.!?])(\s|$)/u', $problem, $m)) {
            return normalizeWs($m[1]);
        }

        return mb_substr($problem, 0, 160);
    }

    return mb_substr(normalizeWs($proposal), 0, 160);
}

function representationA(string $title, string $proposal): string
{
    return "Title: {$title}\nDescription: ".normalizeWs($proposal);
}

function representationB(string $title, string $proposal): string
{
    $s = extractSections($proposal);
    $parts = ["Title: {$title}"];
    if ($s['problem'] !== '') {
        $parts[] = 'Problem: '.$s['problem'];
    }
    if ($s['objectives'] !== '') {
        $parts[] = 'Objectives: '.$s['objectives'];
    }
    if ($s['scope'] !== '') {
        $parts[] = 'Scope: '.$s['scope'];
    }

    return implode("\n", $parts);
}

function representationC(string $title, string $proposal): string
{
    return "Title: {$title}\nDescription: ".shortDescription($title, $proposal);
}

function representationD(string $title, string $proposal): string
{
    return "Title: {$title}";
}

function cosine(array $a, array $b): ?float
{
    $n = count($a);
    if ($n === 0 || $n !== count($b)) {
        return null;
    }
    $dot = 0.0;
    $magA = 0.0;
    $magB = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $dot += $a[$i] * $b[$i];
        $magA += $a[$i] * $a[$i];
        $magB += $b[$i] * $b[$i];
    }
    if ($magA <= 0 || $magB <= 0) {
        return null;
    }
    $score = $dot / (sqrt($magA) * sqrt($magB));

    return max(0.0, min(1.0, $score));
}

function embed(string $baseUrl, string $model, array $texts): array
{
    $payload = json_encode([
        'model' => $model,
        'input' => $texts,
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init(rtrim($baseUrl, '/').'/api/embed');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    if ($body === false || $status < 200 || $status >= 300) {
        throw new RuntimeException("embed failed status={$status} err={$err} body=".substr((string) $body, 0, 200));
    }

    $json = json_decode($body, true);
    if (! is_array($json['embeddings'] ?? null) || count($json['embeddings']) !== count($texts)) {
        throw new RuntimeException('bad embeddings payload');
    }

    return $json['embeddings'];
}

$parkingProposal = <<<'TXT'
Problem Statement
Students and staff waste time searching for empty parking spots on campus during peak hours.

Objectives
• Show live parking availability
• Allow short-term spot reservations
• Reduce congestion at campus gates

Scope
• In scope: campus lots with sensors. Out of scope: city-wide public parking.

Initial Functional Requirements
• Display available spots on a map
• Reserve a spot for up to two hours
• Notify users when a reservation expires
TXT;

$attendanceProposal = <<<'TXT'
Problem Statement
Manual attendance tracking is slow and error-prone for large university classes.

Objectives
• Build a mobile attendance workflow
• Provide milestone tracking for seminar deliverables
• Reduce proxy attendance fraud

Scope
• In scope: classroom check-in for enrolled students. Out of scope: payroll systems.

Initial Functional Requirements
• Scan QR codes for check-in
• Store attendance records per session
• Export reports for supervisors
TXT;

$portalProposal = <<<'TXT'
Problem Statement
Students struggle to find course materials, announcements, and deadlines in one place.

Objectives
• Centralize campus announcements
• Provide course material downloads
• Remind students about deadlines

Scope
• In scope: student portal for announcements and files. Out of scope: grade calculation.

Initial Functional Requirements
• Role-based login for students
• Post and browse announcements
• Upload and download course files
TXT;

$iotProposal = <<<'TXT'
Problem Statement
University labs lack real-time monitoring of equipment status and environmental conditions.

Objectives
• Track temperature and humidity in labs
• Alert technicians when thresholds are exceeded
• Log historical sensor readings

Scope
• In scope: selected engineering labs. Out of scope: campus HVAC control.

Initial Functional Requirements
• Ingest IoT sensor telemetry
• Show live dashboards
• Send threshold alerts
TXT;

$hospitalProposal = <<<'TXT'
Problem Statement
Hospital appointment booking is inefficient and leads to long waiting times for patients.

Objectives
• Allow patients to book appointments online
• Reduce no-show rates with reminders
• Help staff manage clinic schedules

Scope
• In scope: outpatient clinics. Out of scope: inpatient ward management.

Initial Functional Requirements
• Patient registration and login
• Book and cancel appointments
• Send reminder notifications
TXT;

$ecommerceProposal = <<<'TXT'
Problem Statement
Small retailers need an online storefront to sell products and manage orders.

Objectives
• List products with prices and stock
• Support checkout and payments
• Track order fulfillment

Scope
• In scope: catalog, cart, and orders. Out of scope: warehouse robotics.

Initial Functional Requirements
• Browse product catalog
• Add items to cart
• Place and track orders
TXT;

$records = [
    'parking' => ['title' => 'Smart Parking Management System', 'proposal' => $parkingProposal],
    'attendance' => ['title' => 'Mobile Attendance System', 'proposal' => $attendanceProposal],
    'portal' => ['title' => 'Campus Student Portal', 'proposal' => $portalProposal],
    'iot' => ['title' => 'IoT Lab Monitor', 'proposal' => $iotProposal],
    'hospital' => ['title' => 'Hospital Appointment Management System', 'proposal' => $hospitalProposal],
    'ecommerce' => ['title' => 'Campus E-Commerce Storefront', 'proposal' => $ecommerceProposal],
];

// Query variants: near-duplicate parking, moderate parking variant, unrelated pairs
$queries = [
    'high_parking' => [
        'label' => 'HIGH vs parking',
        'expected' => 'high',
        'target' => 'parking',
        'title' => 'AI Campus Parking Spot Finder',
        'proposal' => <<<'TXT'
Problem Statement
Finding empty campus parking spaces is difficult during peak lecture hours.

Objectives
• Detect free parking spots in real time
• Let students reserve a nearby spot
• Cut waiting time at campus entrances

Scope
• Campus parking lots only.

Initial Functional Requirements
• Live spot map
• Short reservation window
• Expiration alerts
TXT,
    ],
    'mod_parking_attendance' => [
        'label' => 'MODERATE parking-ish vs attendance',
        'expected' => 'moderate_or_low',
        'target' => 'attendance',
        'title' => 'Smart Classroom Check-In with Location Sensors',
        'proposal' => <<<'TXT'
Problem Statement
Professors need reliable ways to confirm students are physically present in class.

Objectives
• Capture presence with campus location signals
• Reduce fake attendance
• Produce weekly attendance summaries

Scope
• Lecture halls on campus.

Initial Functional Requirements
• Location-assisted check-in
• Attendance history per course
• Supervisor reports
TXT,
    ],
    'unrelated_parking_hospital' => [
        'label' => 'UNRELATED parking query vs hospital',
        'expected' => 'unrelated',
        'target' => 'hospital',
        'title' => 'AI Campus Parking Spot Finder',
        'proposal' => $parkingProposal,
    ],
    'unrelated_parking_ecommerce' => [
        'label' => 'UNRELATED parking query vs ecommerce',
        'expected' => 'unrelated',
        'target' => 'ecommerce',
        'title' => 'AI Campus Parking Spot Finder',
        'proposal' => $parkingProposal,
    ],
    'unrelated_iot_ecommerce' => [
        'label' => 'UNRELATED iot query vs ecommerce',
        'expected' => 'unrelated',
        'target' => 'ecommerce',
        'title' => 'IoT Lab Environment Monitor',
        'proposal' => $iotProposal,
    ],
    'high_attendance' => [
        'label' => 'HIGH vs attendance',
        'expected' => 'high',
        'target' => 'attendance',
        'title' => 'QR-Based Class Attendance Tracker',
        'proposal' => <<<'TXT'
Problem Statement
Taking attendance manually wastes lecture time and is easy to fake.

Objectives
• Speed up class check-in
• Prevent proxy attendance
• Keep accurate session records

Scope
• University courses and seminars.

Initial Functional Requirements
• QR check-in
• Session attendance list
• Exportable reports
TXT,
    ],
    'mod_portal_attendance' => [
        'label' => 'MODERATE portal vs attendance',
        'expected' => 'moderate_or_low',
        'target' => 'attendance',
        'title' => 'Campus Student Portal',
        'proposal' => $portalProposal,
    ],
    'high_hospital' => [
        'label' => 'HIGH vs hospital',
        'expected' => 'high',
        'target' => 'hospital',
        'title' => 'Clinic Appointment Booking Platform',
        'proposal' => <<<'TXT'
Problem Statement
Patients wait too long because clinic appointments are booked by phone and paper.

Objectives
• Online booking for outpatient visits
• Reminder messages before appointments
• Staff schedule overview

Scope
• Outpatient clinics.

Initial Functional Requirements
• Book appointments
• Cancel or reschedule
• Reminder notifications
TXT,
    ],
];

$reps = [
    'A_full' => 'representationA',
    'B_sections' => 'representationB',
    'C_short' => 'representationC',
    'D_title' => 'representationD',
];

function runMatrix(string $baseUrl, string $model, array $records, array $queries, array $reps, bool $usePrefixes): array
{
    $results = [];

    foreach ($reps as $repName => $fn) {
        $docTexts = [];
        $docKeys = [];
        foreach ($records as $key => $rec) {
            $body = $fn($rec['title'], $rec['proposal']);
            $docTexts[] = $usePrefixes ? 'search_document: '.$body : $body;
            $docKeys[] = $key;
        }

        $queryBodies = [];
        foreach ($queries as $qKey => $q) {
            $body = $fn($q['title'], $q['proposal']);
            $queryBodies[$qKey] = $usePrefixes ? 'search_query: '.$body : $body;
        }

        // Embed all docs + all queries in one batch where possible
        $allTexts = array_merge($docTexts, array_values($queryBodies));
        $vectors = embed($baseUrl, $model, $allTexts);
        $docVectors = array_slice($vectors, 0, count($docTexts));
        $queryVectors = array_slice($vectors, count($docTexts));
        $queryKeys = array_keys($queryBodies);

        foreach ($queryKeys as $qi => $qKey) {
            $q = $queries[$qKey];
            $targetIdx = array_search($q['target'], $docKeys, true);
            $score = cosine($queryVectors[$qi], $docVectors[$targetIdx]);

            // Also score vs all docs for discrimination gap
            $allScores = [];
            foreach ($docKeys as $di => $dKey) {
                $allScores[$dKey] = round(cosine($queryVectors[$qi], $docVectors[$di]) ?? 0, 4);
            }
            arsort($allScores);

            $results[] = [
                'model' => $model,
                'prefix' => $usePrefixes,
                'rep' => $repName,
                'pair' => $q['label'],
                'expected' => $q['expected'],
                'target_score' => round($score ?? 0, 4),
                'top' => array_slice($allScores, 0, 3, true),
                'spread' => round(max($allScores) - min($allScores), 4),
                'unrelated_max' => max(
                    $allScores['hospital'] ?? 0,
                    $allScores['ecommerce'] ?? 0,
                    $allScores['iot'] ?? 0,
                ),
            ];
        }
    }

    return $results;
}

$models = ['nomic-embed-text'];
// Check if all-minilm is available
$tags = @file_get_contents($baseUrl.'/api/tags');
if ($tags && str_contains($tags, 'all-minilm')) {
    $models[] = 'all-minilm';
} else {
    echo "NOTE: all-minilm not pulled; skipping model comparison unless you run: ollama pull all-minilm\n";
}

foreach ($models as $model) {
    foreach ([false, true] as $prefix) {
        echo "\n======= MODEL={$model} PREFIX=".($prefix ? 'yes' : 'no')." =======\n";
        $rows = runMatrix($baseUrl, $model, $records, $queries, $reps, $prefix);
        foreach ($rows as $row) {
            $topStr = [];
            foreach ($row['top'] as $k => $v) {
                $topStr[] = "{$k}={$v}";
            }
            echo sprintf(
                "%-10s | %-42s | target=%.4f | spread=%.4f | top: %s\n",
                $row['rep'],
                $row['pair'],
                $row['target_score'],
                $row['spread'],
                implode(', ', $topStr)
            );
        }

        // Summary discrimination metrics per representation
        echo "-- summary --\n";
        foreach (array_keys($reps) as $repName) {
            $subset = array_values(array_filter($rows, fn ($r) => $r['rep'] === $repName));
            $high = array_values(array_filter($subset, fn ($r) => $r['expected'] === 'high'));
            $unrel = array_values(array_filter($subset, fn ($r) => $r['expected'] === 'unrelated'));
            $highAvg = $high ? array_sum(array_column($high, 'target_score')) / count($high) : 0;
            $unrelAvg = $unrel ? array_sum(array_column($unrel, 'target_score')) / count($unrel) : 0;
            $gap = $highAvg - $unrelAvg;
            $highMin = $high ? min(array_column($high, 'target_score')) : 0;
            $unrelMax = $unrel ? max(array_column($unrel, 'target_score')) : 0;
            echo sprintf(
                "%s: high_avg=%.4f unrel_avg=%.4f gap=%.4f high_min=%.4f unrel_max=%.4f separable=%s\n",
                $repName,
                $highAvg,
                $unrelAvg,
                $gap,
                $highMin,
                $unrelMax,
                $highMin > $unrelMax ? 'YES' : 'NO'
            );
        }
    }
}

echo "\nDone.\n";
