<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // GATEWAYS TABLE — remover colunas dos gateways removidos
        // =====================================================================
        Schema::table('gateways', function (Blueprint $table) {
            $columnsToDrop = [];

            // SuitPay
            foreach (['suitpay_uri', 'suitpay_cliente_id', 'suitpay_cliente_secret'] as $col) {
                if (Schema::hasColumn('gateways', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // Stripe
            foreach (['stripe_production', 'stripe_public_key', 'stripe_secret_key', 'stripe_webhook_key'] as $col) {
                if (Schema::hasColumn('gateways', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // BSPay
            foreach (['bspay_uri', 'bspay_cliente_id', 'bspay_cliente_secret'] as $col) {
                if (Schema::hasColumn('gateways', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // SharkPay (public_key / private_key)
            foreach (['public_key', 'private_key'] as $col) {
                if (Schema::hasColumn('gateways', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // Mercadopago
            foreach (['mp_client_id', 'mp_client_secret', 'mp_public_key', 'mp_access_token'] as $col) {
                if (Schema::hasColumn('gateways', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // DigitoPay
            foreach (['digitopay_uri', 'digitopay_cliente_id', 'digitopay_cliente_secret'] as $col) {
                if (Schema::hasColumn('gateways', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Garantir que as colunas TriboPay existam
        Schema::table('gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('gateways', 'tribopay_uri')) {
                $table->string('tribopay_uri', 191)->nullable()->after('id');
            }
            if (!Schema::hasColumn('gateways', 'tribopay_cliente_id')) {
                $table->string('tribopay_cliente_id', 191)->nullable()->after('tribopay_uri');
            }
            if (!Schema::hasColumn('gateways', 'tribopay_cliente_secret')) {
                $table->string('tribopay_cliente_secret', 191)->nullable()->after('tribopay_cliente_id');
            }
            if (!Schema::hasColumn('gateways', 'tribopay_api_token')) {
                $table->string('tribopay_api_token', 191)->nullable()->after('tribopay_cliente_secret');
            }
        });

        // =====================================================================
        // SETTINGS TABLE — remover colunas de enable dos gateways removidos
        // =====================================================================
        Schema::table('settings', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach ([
                'suitpay_is_enable',
                'stripe_is_enable',
                'bspay_is_enable',
                'sharkpay_is_enable',
                'mercadopago_is_enable',
                'digitopay_is_enable',
            ] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Garantir que tribopay_is_enable exista e esteja ativo
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'tribopay_is_enable')) {
                $table->tinyInteger('tribopay_is_enable')->default(1);
            }
        });

        // Ativar TriboPay por padrão
        \Illuminate\Support\Facades\DB::table('settings')->update([
            'tribopay_is_enable' => 1,
        ]);
    }

    public function down(): void
    {
        // Restaurar colunas removidas da tabela gateways
        Schema::table('gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('gateways', 'suitpay_uri')) {
                $table->string('suitpay_uri', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'suitpay_cliente_id')) {
                $table->string('suitpay_cliente_id', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'suitpay_cliente_secret')) {
                $table->string('suitpay_cliente_secret', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'stripe_production')) {
                $table->tinyInteger('stripe_production')->default(0);
            }
            if (!Schema::hasColumn('gateways', 'stripe_public_key')) {
                $table->string('stripe_public_key', 255)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'stripe_secret_key')) {
                $table->string('stripe_secret_key', 255)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'stripe_webhook_key')) {
                $table->string('stripe_webhook_key', 255)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'bspay_uri')) {
                $table->string('bspay_uri', 255)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'bspay_cliente_id')) {
                $table->string('bspay_cliente_id', 255)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'bspay_cliente_secret')) {
                $table->string('bspay_cliente_secret', 255)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'public_key')) {
                $table->string('public_key', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'private_key')) {
                $table->string('private_key', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'mp_client_id')) {
                $table->string('mp_client_id', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'mp_client_secret')) {
                $table->string('mp_client_secret', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'mp_public_key')) {
                $table->string('mp_public_key', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'mp_access_token')) {
                $table->string('mp_access_token', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'digitopay_uri')) {
                $table->string('digitopay_uri', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'digitopay_cliente_id')) {
                $table->string('digitopay_cliente_id', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'digitopay_cliente_secret')) {
                $table->string('digitopay_cliente_secret', 191)->nullable();
            }
        });

        // Restaurar colunas removidas da tabela settings
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'suitpay_is_enable')) {
                $table->tinyInteger('suitpay_is_enable')->default(0);
            }
            if (!Schema::hasColumn('settings', 'stripe_is_enable')) {
                $table->tinyInteger('stripe_is_enable')->default(0);
            }
            if (!Schema::hasColumn('settings', 'bspay_is_enable')) {
                $table->tinyInteger('bspay_is_enable')->default(0);
            }
            if (!Schema::hasColumn('settings', 'sharkpay_is_enable')) {
                $table->tinyInteger('sharkpay_is_enable')->default(0);
            }
            if (!Schema::hasColumn('settings', 'mercadopago_is_enable')) {
                $table->tinyInteger('mercadopago_is_enable')->default(0);
            }
            if (!Schema::hasColumn('settings', 'digitopay_is_enable')) {
                $table->tinyInteger('digitopay_is_enable')->default(0);
            }
        });
    }
};
