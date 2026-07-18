<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/config.php';
require_once __DIR__ . '/../shared/response.php';
require_once __DIR__ . '/../shared/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'error' => 'METHOD_NOT_ALLOWED',
        'message' => 'Utilize o método POST.',
    ], 405);
}

$expectedApiKey = getenv('METEO_API_KEY') ?: '';
$receivedApiKey = (string) ($_POST['api_key'] ?? '');

if (
    $expectedApiKey === '' ||
    $receivedApiKey === '' ||
    !hash_equals($expectedApiKey, $receivedApiKey)
) {
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_API_KEY',
        'message' => 'API key inválida.',
    ], 401);
}

$requiredFields = [
    'timestamp_estacao',
    'estacao_id',
    'localizacao',
    'boot_count',
    'uptime_s',
];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || trim((string) $_POST[$field]) === '') {
        jsonResponse([
            'success' => false,
            'error' => 'MISSING_FIELD',
            'message' => "Campo obrigatório ausente: {$field}.",
        ], 422);
    }
}

$timestampEstacao = trim((string) $_POST['timestamp_estacao']);
$estacaoId = trim((string) $_POST['estacao_id']);
$localizacao = trim((string) $_POST['localizacao']);

$bootCount = filter_var($_POST['boot_count'], FILTER_VALIDATE_INT);
$uptimeS = filter_var($_POST['uptime_s'], FILTER_VALIDATE_INT);

if ($bootCount === false || $bootCount < 0 || $uptimeS === false || $uptimeS < 0) {
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_COUNTER',
        'message' => 'boot_count ou uptime_s inválido.',
    ], 422);
}

$date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $timestampEstacao);

if (!$date || $date->format('Y-m-d H:i:s') !== $timestampEstacao) {
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_TIMESTAMP',
        'message' => 'timestamp_estacao deve usar YYYY-MM-DD HH:MM:SS.',
    ], 422);
}

function postFloat(string $field): float
{
    return isset($_POST[$field]) ? (float) $_POST[$field] : 0.0;
}

function postInt(string $field): int
{
    return isset($_POST[$field]) ? (int) $_POST[$field] : 0;
}

$tempBme = postFloat('temp_bme_c');
$umidBme = postFloat('umid_bme_pct');
$pressao = postFloat('pressao_hpa');
$lux = postFloat('lux');
$tempDht = postFloat('temp_dht_c');
$umidDht = postFloat('umid_dht_pct');
$ventoMedio = postFloat('vento_medio_60s');
$ventoRajada = postFloat('vento_rajada_60s');
$direcao = postFloat('direcao_graus');
$pulsosChuva = postInt('pulsos_chuva_60s');
$chuva24h = postFloat('chuva_24h_mm');

if ($umidBme < 0 || $umidBme > 100 || $umidDht < 0 || $umidDht > 100) {
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_HUMIDITY',
        'message' => 'Umidade fora do intervalo de 0 a 100%.',
    ], 422);
}

if ($direcao < 0 || $direcao >= 360) {
    jsonResponse([
        'success' => false,
        'error' => 'INVALID_WIND_DIRECTION',
        'message' => 'Direção do vento deve estar entre 0 e menor que 360 graus.',
    ], 422);
}

try {
    $connection = databaseConnection();

        $sql = '
        INSERT IGNORE INTO meteo_dados (
            timestamp_estacao,
            estacao_id,
            localizacao,
            boot_count,
            uptime_s,
            temp_bme_c,
            umid_bme_pct,
            pressao_hpa,
            lux,
            temp_dht_c,
            umid_dht_pct,
            vento_medio_60s,
            vento_rajada_60s,
            direcao_graus,
            pulsos_chuva_60s,
            chuva_24h_mm
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ';

    $statement = $connection->prepare($sql);

    $statement->bind_param(
        'sssiidddddddddid',
        $timestampEstacao,
        $estacaoId,
        $localizacao,
        $bootCount,
        $uptimeS,
        $tempBme,
        $umidBme,
        $pressao,
        $lux,
        $tempDht,
        $umidDht,
        $ventoMedio,
        $ventoRajada,
        $direcao,
        $pulsosChuva,
        $chuva24h
    );

    $statement->execute();

    $novoRegistro = $statement->affected_rows === 1;
    $registroId = $novoRegistro ? $connection->insert_id : 0;

    $statement->close();

    if (!$novoRegistro) {
        $select = $connection->prepare('
            SELECT id
            FROM meteo_dados
            WHERE estacao_id = ?
              AND timestamp_estacao = ?
              AND boot_count = ?
              AND uptime_s = ?
            LIMIT 1
        ');

        $select->bind_param(
            'ssii',
            $estacaoId,
            $timestampEstacao,
            $bootCount,
            $uptimeS
        );

        $select->execute();

        $result = $select->get_result();
        $registroExistente = $result->fetch_assoc();

        $registroId = (int) ($registroExistente['id'] ?? 0);

        $select->close();
    }

    $connection->close();

    jsonResponse([
        'success' => true,
        'status' => $novoRegistro ? 'inserted' : 'duplicate',
        'registro_id' => $registroId,
        'estacao_id' => $estacaoId,
        'timestamp_estacao' => $timestampEstacao,
        'server_time' => date(DATE_ATOM),
    ], $novoRegistro ? 201 : 200);
} catch (Throwable $exception) {
    error_log($exception->getMessage());

    jsonResponse([
        'success' => false,
        'error' => 'DATABASE_ERROR',
        'message' => 'Não foi possível gravar o registro.',
        'server_time' => date(DATE_ATOM),
    ], 500);
}
