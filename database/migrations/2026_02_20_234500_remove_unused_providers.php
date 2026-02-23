<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // GAMES_KEYS TABLE — remover colunas dos provedores removidos
        // =====================================================================
        Schema::table('games_keys', function (Blueprint $table) {
            $columnsToDrop = [];

            // Salsa
            foreach (['salsa_base_uri', 'salsa_pn', 'salsa_key'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // Vibra
            foreach (['vibra_site_id', 'vibra_game_mode'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // WorldSlot
            foreach (['worldslot_agent_code', 'worldslot_agent_token', 'worldslot_agent_secret_key', 'worldslot_api_endpoint'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // Games2 API
            foreach (['games2_agent_code', 'games2_agent_token', 'games2_agent_secret_key', 'games2_api_endpoint'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // Evergame
            foreach (['evergame_agent_code', 'evergame_agent_token', 'evergame_api_endpoint'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // Venix / Shark Connect
            foreach (['venix_agent_code', 'venix_agent_token', 'venix_agent_secret'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // VenixCG
            foreach (['venixcg_api_url', 'venixcg_agent_code', 'venixcg_agent_token', 'venixcg_agent_secret'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // Play Gaming
            foreach (['play_gaming_hall', 'play_gaming_key', 'play_gaming_login'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            // PlayiGaming (pig_)
            foreach (['pig_agent_code', 'pig_agent_token', 'pig_agent_secret'] as $col) {
                if (Schema::hasColumn('games_keys', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('games_keys', function (Blueprint $table) {
            // Salsa
            if (!Schema::hasColumn('games_keys', 'salsa_base_uri')) {
                $table->string('salsa_base_uri', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'salsa_pn')) {
                $table->string('salsa_pn', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'salsa_key')) {
                $table->string('salsa_key', 191)->nullable();
            }

            // Vibra
            if (!Schema::hasColumn('games_keys', 'vibra_site_id')) {
                $table->string('vibra_site_id', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'vibra_game_mode')) {
                $table->string('vibra_game_mode', 191)->nullable();
            }

            // WorldSlot
            if (!Schema::hasColumn('games_keys', 'worldslot_agent_code')) {
                $table->string('worldslot_agent_code', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'worldslot_agent_token')) {
                $table->string('worldslot_agent_token', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'worldslot_agent_secret_key')) {
                $table->string('worldslot_agent_secret_key', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'worldslot_api_endpoint')) {
                $table->string('worldslot_api_endpoint', 191)->nullable();
            }

            // Games2 API
            if (!Schema::hasColumn('games_keys', 'games2_agent_code')) {
                $table->string('games2_agent_code', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'games2_agent_token')) {
                $table->string('games2_agent_token', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'games2_agent_secret_key')) {
                $table->string('games2_agent_secret_key', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'games2_api_endpoint')) {
                $table->string('games2_api_endpoint', 191)->nullable();
            }

            // Evergame
            if (!Schema::hasColumn('games_keys', 'evergame_agent_code')) {
                $table->string('evergame_agent_code', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'evergame_agent_token')) {
                $table->string('evergame_agent_token', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'evergame_api_endpoint')) {
                $table->string('evergame_api_endpoint', 191)->nullable();
            }

            // Venix
            if (!Schema::hasColumn('games_keys', 'venix_agent_code')) {
                $table->string('venix_agent_code', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'venix_agent_token')) {
                $table->string('venix_agent_token', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'venix_agent_secret')) {
                $table->string('venix_agent_secret', 191)->nullable();
            }

            // Play Gaming
            if (!Schema::hasColumn('games_keys', 'play_gaming_hall')) {
                $table->string('play_gaming_hall', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'play_gaming_key')) {
                $table->string('play_gaming_key', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'play_gaming_login')) {
                $table->string('play_gaming_login', 191)->nullable();
            }

            // PlayiGaming
            if (!Schema::hasColumn('games_keys', 'pig_agent_code')) {
                $table->string('pig_agent_code', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'pig_agent_token')) {
                $table->string('pig_agent_token', 191)->nullable();
            }
            if (!Schema::hasColumn('games_keys', 'pig_agent_secret')) {
                $table->string('pig_agent_secret', 191)->nullable();
            }
        });
    }
};
