<?php
/**
 * Exibe as ultimas linhas do log do Laravel.
 * USO: https://seusite.com/logview.php?key=viperpro-migrate-2026-x9k2m&lines=100
 */

$SECRET_KEY = 'viperpro-migrate-2026-x9k2m';

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    http_response_code(403);
    echo "Acesso negado.\n";
    exit(1);
}

$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 100;
$filter = isset($_GET['filter']) ? $_GET['filter'] : null;

$logFile = __DIR__ . '/../storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "Arquivo de log nao encontrado: $logFile\n";
    exit(1);
}

// Ler ultimas N linhas
$allLines = file($logFile, FILE_IGNORE_NEW_LINES);
$total = count($allLines);
$lastLines = array_slice($allLines, max(0, $total - $lines));

echo "=== LOG LARAVEL (ultimas $lines de $total linhas) ===\n\n";

foreach ($lastLines as $line) {
    if ($filter && stripos($line, $filter) === false) continue;
    echo $line . "\n";
}
