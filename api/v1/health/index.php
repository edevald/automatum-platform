<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/config.php';
require_once __DIR__ . '/../shared/response.php';
require_once __DIR__ . '/../shared/database.php';

try {
    $connection = databaseConnection();

    $result = $connection->query(
        "SELECT DATABASE() AS database_name, NOW() AS database_time"
    );

    $database = $result->fetch_assoc();

    $connection->close();

    jsonResponse([
        'success' => true,
        'status' => 'ok',
        'version' => API_VERSION,
        'database' => [
            'status' => 'connected',
            'name' => $database['database_name'] ?? null,
            'time' => $database['database_time'] ?? null,
        ],
        'server_time' => date(DATE_ATOM),
    ]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());

    jsonResponse([
        'success' => false,
        'status' => 'error',
        'version' => API_VERSION,
        'database' => [
            'status' => 'disconnected',
        ],
        'server_time' => date(DATE_ATOM),
    ], 503);
}