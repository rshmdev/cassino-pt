<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar coluna tribopay se nao existir
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'tribopay_is_enable')) {
                $table->tinyInteger('tribopay_is_enable')->default(0)->after('bspay_is_enable');
            }
        });

        // Desativar todos os outros gateways, ativar apenas tribopay
        DB::table('settings')->update([
            'suitpay_is_enable'     => 0,
            'stripe_is_enable'      => 0,
            'bspay_is_enable'       => 0,
            'sharkpay_is_enable'    => 0,
            'mercadopago_is_enable' => 0,
            'digitopay_is_enable'   => 0,
            'tribopay_is_enable'    => 1,
        ]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('tribopay_is_enable');
        });
    }
};
