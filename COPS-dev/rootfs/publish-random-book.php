<?php
/**
 * Publishes the hourly random book as a separate Home Assistant sensor.
 */

require_once '/cops-publisher-helpers.php';

$dbPath = '/cops/library/metadata.db';
$statePath = '/data/cops-random-book.json';

if (!file_exists($dbPath)) {
    error_log("HA COPS Random Book: Database not found at {$dbPath}");
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $latestId = (int)($pdo->query('SELECT id FROM books ORDER BY timestamp DESC, id DESC LIMIT 1')->fetchColumn() ?: 0);
    $currentHour = gmdate('Y-m-d-H');
    $forcePublish = in_array('--startup', $argv, true);
    $randomState = null;

    if (is_file($statePath)) {
        $stateContents = file_get_contents($statePath);
        if ($stateContents === false) {
            error_log("HA COPS Random Book: Could not read state at {$statePath}.");
            exit(1);
        }

        $storedState = json_decode($stateContents, true);
        if (
            is_array($storedState)
            && is_string($storedState['hour'] ?? null)
            && array_key_exists('book_id', $storedState)
            && ($storedState['book_id'] === null || is_int($storedState['book_id']))
            && (!array_key_exists('published', $storedState) || is_bool($storedState['published']))
        ) {
            $randomState = $storedState;
        } else {
            error_log("HA COPS Random Book: Invalid state at {$statePath}; selecting a new book.");
        }
    }

    // Keep a valid selection for the hour, unless it is now the latest or no longer exists.
    $rotate = !is_array($randomState) || $randomState['hour'] !== $currentHour;
    $randomBook = false;
    if (!$rotate && is_int($randomState['book_id']) && $randomState['book_id'] > 0) {
        $validateStmt = $pdo->prepare('SELECT id, timestamp FROM books WHERE id = :book_id AND id != :latest_id');
        $validateStmt->execute([
            'book_id' => $randomState['book_id'],
            'latest_id' => $latestId
        ]);
        $randomBook = $validateStmt->fetch();
        $rotate = $randomBook === false;
    } elseif (!$rotate && $randomState['book_id'] === null) {
        // Check whether a second book has become available since the last attempt.
        $randomStmt = $pdo->prepare('SELECT id, timestamp FROM books WHERE id != :latest_id ORDER BY RANDOM() LIMIT 1');
        $randomStmt->execute(['latest_id' => $latestId]);
        $randomBook = $randomStmt->fetch();
        $rotate = $randomBook !== false;
    } elseif (!$rotate) {
        $rotate = true;
    }

    if ($rotate) {
        if ($randomBook === false) {
            $randomStmt = $pdo->prepare('SELECT id, timestamp FROM books WHERE id != :latest_id ORDER BY RANDOM() LIMIT 1');
            $randomStmt->execute(['latest_id' => $latestId]);
            $randomBook = $randomStmt->fetch();
        }

        $randomState = [
            'hour' => $currentHour,
            'book_id' => $randomBook === false ? null : (int)$randomBook['id'],
            'published' => false
        ];
        $temporaryStatePath = $statePath . '.tmp';
        $stateJson = json_encode($randomState, JSON_THROW_ON_ERROR);
        if (
            file_put_contents($temporaryStatePath, $stateJson, LOCK_EX) === false
            || !rename($temporaryStatePath, $statePath)
        ) {
            if (is_file($temporaryStatePath)) {
                unlink($temporaryStatePath);
            }
            error_log("HA COPS Random Book: Could not save state at {$statePath}.");
            exit(1);
        }
    }

    if (!$rotate && !$forcePublish && ($randomState['published'] ?? false)) {
        exit(0);
    }

    $randomId = (int)($randomBook['id'] ?? 0);
    $randomDate = '';
    if (is_string($randomBook['timestamp'] ?? null) && $randomBook['timestamp'] !== '') {
        try {
            $randomDate = (new DateTimeImmutable($randomBook['timestamp']))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z');
        } catch (Exception $e) {
            error_log("HA COPS Random Book: Could not parse book timestamp: " . $e->getMessage());
        }
    }

    $bookDetails = [];
    if ($randomId > 0) {
        $ingressUrl = fetchCopsIngressUrl('HA COPS Random Book');
        if ($ingressUrl === false) {
            exit(1);
        }
        $port = (string)getenv('COPS_PORT');
        $bookDetails = fetchCopsBookDetails(
            $randomId,
            'random',
            $port,
            $ingressUrl,
            'HA COPS Random Book'
        );
    }
    if ($randomId > 0 && $bookDetails === []) {
        exit(1);
    }
    $randomTitle = $bookDetails['title'] ?? 'None';

    $payload = [
        'state' => $randomId > 0 ? (string)$randomId : 'none',
        'attributes' => [
            'friendly_name' => 'HA COPS Random Book',
            'icon' => 'mdi:book-open-page-variant',
            'random_book_id' => $randomId > 0 ? $randomId : null,
            'random_book_title' => $randomTitle,
            'random_book_author' => $bookDetails['author'] ?? 'Unknown',
            'random_book_date_added' => $randomDate,
            'random_book_detail_url' => $bookDetails['detail_url'] ?? '',
            'random_book_cover_thumb_url' => $bookDetails['thumbnail_url'] ?? '',
            'random_book_updated' => gmdate('Y-m-d\TH:i:s\Z')
        ]
    ];

    if (!publishHaState('sensor.cops_random_book', $payload, 'HA COPS Random Book')) {
        exit(1);
    }

    $randomState['published'] = true;
    $temporaryStatePath = $statePath . '.tmp';
    $stateJson = json_encode($randomState, JSON_THROW_ON_ERROR);
    if (
        file_put_contents($temporaryStatePath, $stateJson, LOCK_EX) === false
        || !rename($temporaryStatePath, $statePath)
    ) {
        if (is_file($temporaryStatePath)) {
            unlink($temporaryStatePath);
        }
        error_log("HA COPS Random Book: Could not save published state at {$statePath}.");
        exit(1);
    }

    echo "HA COPS Random Book: Published successfully ('{$randomTitle}').\n";
} catch (Exception $e) {
    error_log("HA COPS Random Book Error: " . $e->getMessage());
    exit(1);
}
