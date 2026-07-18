<?php

declare(strict_types=1);

function databaseConnection(): mysqli
{
    $host = getenv('DB_HOST') ?: 'mariadb';
    $port = (int) (getenv('DB_PORT') ?: 3306);
    $database = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $password = getenv('DB_PASSWORD') ?: '';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $connection = new mysqli(
        $host,
        $user,
        $password,
        $database,
        $port
    );

    $connection->set_charset('utf8mb4');

    return $connection;
}