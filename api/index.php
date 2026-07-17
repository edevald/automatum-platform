<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => true,
    'service' => 'automatum-api',
    'php_version' => PHP_VERSION,
    'time' => date(DATE_ATOM),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);