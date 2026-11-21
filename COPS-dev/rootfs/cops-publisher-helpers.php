<?php

function copsMakeAbsoluteUrl(mixed $url, string $baseUrl): string
{
    if (!is_string($url) || $url === '') {
        return '';
    }
    if (preg_match('~^https?://~i', $url) === 1) {
        return $url;
    }
    if (str_starts_with($url, '//')) {
        return 'http:' . $url;
    }
    return $baseUrl . '/' . ltrim($url, '/');
}

function fetchCopsIngressUrl(string $logPrefix): string|false
{
    $supervisorToken = getenv('SUPERVISOR_TOKEN');
    if (!$supervisorToken) {
        error_log("{$logPrefix}: SUPERVISOR_TOKEN not set in environment.");
        return false;
    }

    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer {$supervisorToken}\r\n",
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];
    $http_response_header = [];
    $response = @file_get_contents(
        'http://supervisor/addons/self/info',
        false,
        stream_context_create($options)
    );

    if ($response === false) {
        error_log("{$logPrefix}: Failed to fetch the add-on ingress URL from the Supervisor API.");
        return false;
    }

    $responseCode = 0;
    foreach ($http_response_header as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
            $responseCode = (int)$matches[1];
        }
    }
    if ($responseCode < 200 || $responseCode >= 300) {
        error_log("{$logPrefix}: Supervisor API returned HTTP {$responseCode} while fetching the add-on ingress URL.");
        return false;
    }

    $responseData = json_decode($response, true);
    $ingressUrl = $responseData['data']['ingress_url'] ?? null;
    if (
        !is_string($ingressUrl)
        || !str_starts_with($ingressUrl, '/')
        || str_starts_with($ingressUrl, '//')
        || rtrim($ingressUrl, '/') === ''
    ) {
        error_log("{$logPrefix}: Supervisor API returned an invalid add-on ingress URL.");
        return false;
    }

    return rtrim($ingressUrl, '/');
}

function fetchCopsBookDetails(
    int $bookId,
    string $bookType,
    string $port,
    string $ingressUrl,
    string $logPrefix
): array
{
    if ($bookId <= 0) {
        return [];
    }

    $apiUrl = "http://localhost:{$port}/restapi/books/{$bookId}";
    $apiOptions = [
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];
    $apiContext = stream_context_create($apiOptions);
    $maxAttempts = 6;
    $retryDelay = 1;
    $apiResponse = false;
    $responseCode = 0;
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $http_response_header = [];
        $responseCode = 0;
        $apiResponse = @file_get_contents($apiUrl, false, $apiContext);
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
                $responseCode = (int)$matches[1];
            }
        }

        if ($apiResponse !== false && $responseCode >= 200 && $responseCode < 300) {
            break;
        }

        if (
            $apiResponse !== false
            && !in_array($responseCode, [502, 503, 504], true)
        ) {
            break;
        }

        if ($attempt < $maxAttempts) {
            sleep($retryDelay);
            $retryDelay = min($retryDelay * 2, 5);
        }
    }

    if ($apiResponse === false) {
        error_log("{$logPrefix}: Failed to fetch {$bookType} book details from REST API after {$maxAttempts} attempts. {$apiUrl}");
        return [];
    }

    if ($responseCode < 200 || $responseCode >= 300) {
        error_log("{$logPrefix}: REST API returned HTTP {$responseCode} for {$bookType} book {$bookId}.");
        return [];
    }

    $apiData = json_decode($apiResponse, true);
    if (!is_array($apiData) || !is_array($apiData['book'] ?? null)) {
        error_log("{$logPrefix}: Invalid REST API response for {$bookType} book {$bookId}.");
        return [];
    }

    $book = $apiData['book'];
    $detailUrl = $book['detailurl'] ?? '';
    if (is_string($detailUrl) && str_starts_with($detailUrl, '/restapi/')) {
        $detailUrl = substr($detailUrl, strlen('/restapi'));
    }
    $thumbnailUrl = copsMakeAbsoluteUrl($book['thumbnailurl'] ?? '', $ingressUrl);
    if ($thumbnailUrl === '') {
        $thumbnailUrl = copsMakeAbsoluteUrl('images/icons/icon144.png', $ingressUrl);
    }

    return [
        'title' => is_string($apiData['title'] ?? null) ? $apiData['title'] : '',
        'author' => is_string($book['authorsName'] ?? null) ? $book['authorsName'] : '',
        'detail_url' => copsMakeAbsoluteUrl($detailUrl, $ingressUrl),
        'thumbnail_url' => $thumbnailUrl
    ];
}

function publishHaState(string $entityId, array $payload, string $logPrefix): bool
{
    $supervisorToken = getenv('SUPERVISOR_TOKEN');
    if (!$supervisorToken) {
        error_log("{$logPrefix}: SUPERVISOR_TOKEN not set in environment.");
        return false;
    }

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer {$supervisorToken}\r\n" .
                "Content-Type: application/json\r\n",
            'content' => json_encode($payload, JSON_THROW_ON_ERROR),
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);
    $maxAttempts = 7;
    $retryDelay = 10;
    $response = false;
    $responseCode = 0;
    $url = "http://supervisor/core/api/states/{$entityId}";

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $http_response_header = [];
        $responseCode = 0;
        $response = @file_get_contents($url, false, $context);
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
                $responseCode = (int)$matches[1];
            }
        }

        if ($response !== false && $responseCode >= 200 && $responseCode < 300) {
            return true;
        }

        if ($responseCode >= 400 && $responseCode < 500 && $responseCode !== 429) {
            break;
        }

        if ($attempt < $maxAttempts) {
            sleep($retryDelay);
            $retryDelay = min($retryDelay * 2, 30);
        }
    }

    $responseDetails = is_string($response) ? substr($response, 0, 500) : 'No response body';
    error_log("{$logPrefix}: Failed to post sensor data after {$attempt} attempt(s) (HTTP {$responseCode}): {$responseDetails}");
    return false;
}
