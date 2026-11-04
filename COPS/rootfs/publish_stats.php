<?php
/**
 * HA COPS Library Stats Publisher
 * Runs via inotify-tools s6 service when /media/books/metadata.db changes.
 */

$dbPath = '/cops/library/metadata.db';

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

    // 2. Fetch details for the latest added book
    $stmt = $pdo->query('
        SELECT b.id, b.title, b.timestamp, a.name AS author_name
        FROM books b
        LEFT JOIN books_authors_link bal ON b.id = bal.book
        LEFT JOIN authors a ON bal.author = a.id
        ORDER BY b.timestamp DESC
        LIMIT 1
    ');
    $latestBook = $stmt->fetch();

    $latestTitle = $latestBook['title'] ?? 'None';
    $latestAuthor = $latestBook['author_name'] ?? 'Unknown';
    $latestDate = $latestBook['timestamp'] ?? '';
    $latestId = $latestBook['id'] ?? 0;
    $latestFolder = intdiv($latestId,1000);
    $latest_cover_url = "/covers/{$latestFolder}/{$latestId}.jpg";


    // Build the Home Assistant sensor payload
    $payload = [
        'state' => (string)$totalBooks,
        'attributes' => [
            'friendly_name' => 'HA COPS Library Stats',
            'icon' => 'mdi:bookshelf',
            'total_books' => $totalBooks,
            'latest_book_title' => $latestTitle,
            'latest_book_author' => $latestAuthor,
            'latest_book_date_added' => $latestDate,
            'latest_book_cover_relative_url' => $latest_cover_url,
            'stats_last_updated' => gmdate('Y-m-d\TH:i:s\Z')
        ]
    ];

    // Fetch the Supervisor token passed automatically into the container environment
    $supervisorToken = getenv('SUPERVISOR_TOKEN');
    if (!$supervisorToken) {
        error_log("HA COPS Stats: SUPERVISOR_TOKEN not set in environment.");
        exit(1);
    }

    // Configure native HTTP stream context (no cURL dependency required)
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Authorization: Bearer {$supervisorToken}\r\n" .
                         "Content-Type: application/json\r\n",
            'content' => json_encode($payload),
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents('http://supervisor/core/api/states/sensor.cops_library_stats', false, $context);

    if ($response === false) {
        error_log("HA COPS Stats: Failed to post sensor data to Supervisor API.");
        exit(1);
    }

    echo "HA COPS Stats: Published successfully ({$totalBooks} books, latest: '{$latestTitle}').\n";

} catch (Exception $e) {
    error_log("HA COPS Stats Error: " . $e->getMessage());
    exit(1);
}