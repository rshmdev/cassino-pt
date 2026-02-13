<?php

/**
 * MIGRATION RUNNER - Acessivel via HTTP
 * 
 * Roda `php artisan migrate --force` quando acessado com a chave correta.
 * Na primeira execucao, limpa as migrations antigas e registra a baseline.
 *
 * USO: https://seusite.com/migrate.php?key=SUA_CHAVE_SECRETA
 * 
 * SEGURANCA: Mude a chave abaixo para algo unico e secreto.
 */

// ============================================================================
// CHAVE DE SEGURANCA - MUDE ISSO!
// ============================================================================
$SECRET_KEY = 'viperpro-migrate-2026-x9k2m';

// ============================================================================
// VERIFICACAO DE SEGURANCA
// ============================================================================
header('Content-Type: text/plain; charset=utf-8');

if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    http_response_code(403);
    echo "Acesso negado.\n";
    exit(1);
}

// ============================================================================
// BOOTSTRAP LARAVEL
// ============================================================================
define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

echo "=== VIPERPRO MIGRATION RUNNER ===\n\n";

// ============================================================================
// VERIFICAR SE PRECISA FAZER SETUP INICIAL (baseline)
// ============================================================================
try {
    if (Schema::hasTable('migrations')) {
        $oldMigrations = DB::table('migrations')
            ->where('migration', 'like', '%create_%')
            ->orWhere('migration', 'like', '%add_%')
            ->count();
        
        $hasBaseline = DB::table('migrations')
            ->where('migration', '0001_01_01_000000_baseline')
            ->exists();
        
        if (!$hasBaseline) {
            echo "[SETUP] Primeira execucao detectada.\n";
            echo "[SETUP] Limpando migrations antigas ($oldMigrations registros)...\n";
            
            // Limpar tabela de migrations e registrar baseline como ja executada
            DB::table('migrations')->truncate();
            DB::table('migrations')->insert([
                'migration' => '0001_01_01_000000_baseline',
                'batch' => 1,
            ]);
            
            echo "[SETUP] Baseline registrada como ja executada.\n";
            echo "[SETUP] Setup inicial concluido!\n\n";
        }
    }
} catch (\Exception $e) {
    echo "[WARN] Erro no setup inicial: " . $e->getMessage() . "\n";
    echo "[WARN] Continuando com migrate normal...\n\n";
}

// ============================================================================
// RODAR MIGRATIONS
// ============================================================================
echo "[MIGRATE] Rodando php artisan migrate --force...\n\n";

try {
    $exitCode = Artisan::call('migrate', [
        '--force' => true,
    ]);

    $output = Artisan::output();
    echo $output;
    
    if ($exitCode === 0) {
        echo "\n[OK] Migrations executadas com sucesso!\n";
    } else {
        echo "\n[ERRO] Migration falhou com codigo: $exitCode\n";
    }
} catch (\Exception $e) {
    echo "\n[ERRO] Excecao durante migration: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// ============================================================================
// LIMPAR CACHE (boa pratica apos deploy)
// ============================================================================
echo "\n[CACHE] Limpando caches...\n";

try {
    Artisan::call('config:cache');
    echo "  - config:cache OK\n";
} catch (\Exception $e) {
    echo "  - config:cache FALHOU: " . $e->getMessage() . "\n";
}

try {
    Artisan::call('route:cache');
    echo "  - route:cache OK\n";
} catch (\Exception $e) {
    echo "  - route:cache FALHOU: " . $e->getMessage() . "\n";
}

try {
    Artisan::call('view:cache');
    echo "  - view:cache OK\n";
} catch (\Exception $e) {
    echo "  - view:cache FALHOU: " . $e->getMessage() . "\n";
}

$elapsed = round(microtime(true) - LARAVEL_START, 2);
echo "\n=== FINALIZADO em {$elapsed}s ===\n";
