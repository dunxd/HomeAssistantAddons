<?php
/**
 * HA COPS Library Stats Publisher
 * Runs via the cops-watcher s6 service when stats need refreshing.
 */

$dbPath = '/cops/library/metadata.db';
require_once '/cops-publisher-helpers.php';

if (!file_exists($dbPath)) {
    error_log("HA COPS Stats: Database not found at {$dbPath}");
    exit(1);
}

try {
    // Open SQLite database in read-only mode using native PDO
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 1. Get total book count
    $totalBooks = (int)$pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
    $totalAuthors = (int)$pdo->query('SELECT COUNT(*) FROM authors')->fetchColumn();
    $totalTags = (int)$pdo->query('SELECT COUNT(*) FROM tags')->fetchColumn();
    $totalSeries = (int)$pdo->query('SELECT COUNT(*) FROM series')->fetchColumn();
    $totalLanguages = (int)$pdo->query('SELECT COUNT(*) FROM languages')->fetchColumn();

    // 2. Fetch details for the latest added book
    $stmt = $pdo->query('
        SELECT b.id, b.title, b.timestamp, a.name AS author_name
        FROM books b
        LEFT JOIN books_authors_link bal ON b.id = bal.book
        LEFT JOIN authors a ON bal.author = a.id
        ORDER BY b.timestamp DESC, b.id DESC
        LIMIT 1
    ');
    $latestBook = $stmt->fetch();

    $latestTitle = $latestBook['title'] ?? 'None';
    $latestAuthor = $latestBook['author_name'] ?? 'Unknown';
    $formatTimestamp = static function (?string $timestamp, string $bookType): string {
        if ($timestamp === null || $timestamp === '') {
            return '';
        }

        try {
            return (new DateTimeImmutable($timestamp))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
        } catch (Exception $e) {
            error_log("HA COPS Stats: Could not parse {$bookType} book timestamp: " . $e->getMessage());
            return '';
        }
    };
    $latestDate = $formatTimestamp($latestBook['timestamp'] ?? null, 'latest');
    $latestId = $latestBook['id'] ?? 0;
    $latestApiBook = [];
    if ((int)$latestId > 0) {
        $ingressUrl = fetchCopsIngressUrl('HA COPS Stats');
        if ($ingressUrl === false) {
            exit(1);
        }
        $port = (string)getenv('COPS_PORT');
        $latestApiBook = fetchCopsBookDetails(
            (int)$latestId,
            'latest',
            $port,
            $ingressUrl,
            'HA COPS Stats'
        );
        if ($latestApiBook === []) {
            exit(1);
        }
    }
    $latestDetailUrl = $latestApiBook['detail_url'] ?? '';
    $latestThumbUrl = $latestApiBook['thumbnail_url'] ?? '';

    // Build the Home Assistant sensor payload
    $payload = [
        'state' => (string)$totalBooks,
        'attributes' => [
            'friendly_name' => 'HA COPS Library Stats',
            'icon' => 'mdi:book-information-variant',
            'total_books' => $totalBooks,
            'total_authors' => $totalAuthors,
            'total_tags' => $totalTags,
            'total_series' => $totalSeries,
            'total_languages' => $totalLanguages,
            'latest_book_title' => $latestTitle,
            'latest_book_author' => $latestAuthor,
            'latest_book_date_added' => $latestDate,
            'latest_book_detail_url' => $latestDetailUrl,
            'latest_book_cover_thumb_url' => $latestThumbUrl,
            'stats_last_updated' => gmdate('Y-m-d\TH:i:s\Z')
        ]
    ];

    if (!publishHaState('sensor.cops_library_stats', $payload, 'HA COPS Stats')) {
        exit(1);
    }

    echo "HA COPS Stats: Published successfully ({$totalBooks} books, latest: '{$latestTitle}').\n";

} catch (Exception $e) {
    error_log("HA COPS Stats Error: " . $e->getMessage());
    exit(1);
}