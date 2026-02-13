<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * BASELINE MIGRATION - 2026-02-12
 * 
 * Esta migration representa o estado completo do banco de dados.
 * Foi gerada a partir de mysqldump do banco local.
 * 
 * No servidor de producao, esta migration deve ser marcada como "ja rodada"
 * na tabela `migrations` para que nao tente recriar as tabelas.
 * 
 * Para marcar como rodada sem executar:
 *   INSERT INTO migrations (migration, batch) VALUES ('0001_01_01_000000_baseline', 1);
 */
return new class extends Migration
{
    public function up(): void
    {
        // ====================================================================
        // USERS & AUTH
        // ====================================================================
        if (!Schema::hasTable('users')) {
            DB::unprepared("
                CREATE TABLE `users` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(191) NOT NULL,
                    `email` varchar(191) NOT NULL,
                    `email_verified_at` timestamp NULL DEFAULT NULL,
                    `password` varchar(191) NOT NULL,
                    `remember_token` varchar(100) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `oauth_id` varchar(191) DEFAULT NULL,
                    `oauth_type` varchar(191) DEFAULT NULL,
                    `avatar` varchar(191) DEFAULT NULL,
                    `last_name` varchar(191) DEFAULT NULL,
                    `cpf` varchar(20) DEFAULT NULL,
                    `phone` varchar(30) DEFAULT NULL,
                    `logged_in` tinyint(4) NOT NULL DEFAULT 0,
                    `banned` tinyint(4) NOT NULL DEFAULT 0,
                    `inviter` int(11) DEFAULT NULL,
                    `inviter_code` varchar(25) DEFAULT NULL,
                    `affiliate_revenue_share` bigint(20) NOT NULL DEFAULT 2,
                    `affiliate_revenue_share_fake` bigint(20) DEFAULT NULL,
                    `affiliate_cpa` decimal(20,2) NOT NULL DEFAULT 10.00,
                    `affiliate_baseline` decimal(20,2) NOT NULL DEFAULT 40.00,
                    `is_demo_agent` tinyint(4) NOT NULL DEFAULT 0,
                    `status` varchar(50) NOT NULL DEFAULT 'active',
                    `language` varchar(191) NOT NULL DEFAULT 'pt_BR',
                    `role_id` int(11) DEFAULT 3,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `users_email_unique` (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            DB::unprepared("
                CREATE TABLE `password_reset_tokens` (
                    `email` varchar(191) NOT NULL,
                    `token` varchar(191) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('personal_access_tokens')) {
            DB::unprepared("
                CREATE TABLE `personal_access_tokens` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `tokenable_type` varchar(191) NOT NULL,
                    `tokenable_id` bigint(20) unsigned NOT NULL,
                    `name` varchar(191) NOT NULL,
                    `token` varchar(64) NOT NULL,
                    `abilities` text DEFAULT NULL,
                    `last_used_at` timestamp NULL DEFAULT NULL,
                    `expires_at` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
                    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('failed_jobs')) {
            DB::unprepared("
                CREATE TABLE `failed_jobs` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(191) NOT NULL,
                    `connection` text NOT NULL,
                    `queue` text NOT NULL,
                    `payload` longtext NOT NULL,
                    `exception` longtext NOT NULL,
                    `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // ROLES & PERMISSIONS (Spatie)
        // ====================================================================
        if (!Schema::hasTable('permissions')) {
            DB::unprepared("
                CREATE TABLE `permissions` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(191) NOT NULL,
                    `guard_name` varchar(191) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('roles')) {
            DB::unprepared("
                CREATE TABLE `roles` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(191) NOT NULL,
                    `guard_name` varchar(191) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('model_has_permissions')) {
            DB::unprepared("
                CREATE TABLE `model_has_permissions` (
                    `permission_id` bigint(20) unsigned NOT NULL,
                    `model_type` varchar(191) NOT NULL,
                    `model_id` bigint(20) unsigned NOT NULL,
                    PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
                    KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
                    CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('model_has_roles')) {
            DB::unprepared("
                CREATE TABLE `model_has_roles` (
                    `role_id` bigint(20) unsigned NOT NULL,
                    `model_type` varchar(191) NOT NULL,
                    `model_id` bigint(20) unsigned NOT NULL,
                    PRIMARY KEY (`role_id`,`model_id`,`model_type`),
                    KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
                    CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('role_has_permissions')) {
            DB::unprepared("
                CREATE TABLE `role_has_permissions` (
                    `permission_id` bigint(20) unsigned NOT NULL,
                    `role_id` bigint(20) unsigned NOT NULL,
                    PRIMARY KEY (`permission_id`,`role_id`),
                    KEY `role_has_permissions_role_id_foreign` (`role_id`),
                    CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
                    CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // WALLETS & FINANCES
        // ====================================================================
        if (!Schema::hasTable('wallets')) {
            DB::unprepared("
                CREATE TABLE `wallets` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL,
                    `currency` varchar(20) NOT NULL,
                    `symbol` varchar(5) NOT NULL,
                    `balance` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `balance_bonus_rollover` decimal(10,2) DEFAULT 0.00,
                    `balance_deposit_rollover` decimal(10,2) DEFAULT 0.00,
                    `balance_withdrawal` decimal(10,2) DEFAULT 0.00,
                    `balance_bonus` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `balance_cryptocurrency` decimal(20,8) NOT NULL DEFAULT 0.00000000,
                    `balance_demo` decimal(20,8) DEFAULT 1000.00000000,
                    `refer_rewards` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `hide_balance` tinyint(1) NOT NULL DEFAULT 0,
                    `active` tinyint(1) NOT NULL DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `total_bet` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `total_won` bigint(20) NOT NULL DEFAULT 0,
                    `total_lose` bigint(20) NOT NULL DEFAULT 0,
                    `last_won` bigint(20) NOT NULL DEFAULT 0,
                    `last_lose` bigint(20) NOT NULL DEFAULT 0,
                    `vip_level` bigint(20) DEFAULT 0,
                    `vip_points` bigint(20) DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY `wallets_user_id_index` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('wallet_changes')) {
            DB::unprepared("
                CREATE TABLE `wallet_changes` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `reason` varchar(100) DEFAULT NULL,
                    `change` varchar(50) DEFAULT NULL,
                    `value_bonus` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `value_total` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `value_roi` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `value_entry` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `refer_rewards` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `game` varchar(191) DEFAULT NULL,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `wallet_changes_user_id_foreign` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('system_wallets')) {
            DB::unprepared("
                CREATE TABLE `system_wallets` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `label` char(32) NOT NULL,
                    `balance` decimal(27,12) NOT NULL DEFAULT 0.000000000000,
                    `balance_min` decimal(27,12) NOT NULL DEFAULT 10000.100000000000,
                    `pay_upto_percentage` decimal(4,2) NOT NULL DEFAULT 45.00,
                    `mode` enum('balance_min','percentage') NOT NULL DEFAULT 'percentage',
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('currencies')) {
            DB::unprepared("
                CREATE TABLE `currencies` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(20) NOT NULL,
                    `code` varchar(3) NOT NULL,
                    `symbol` varchar(5) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('currency_alloweds')) {
            DB::unprepared("
                CREATE TABLE `currency_alloweds` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `currency_id` bigint(20) unsigned NOT NULL,
                    `active` tinyint(1) NOT NULL DEFAULT 1,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `currency_alloweds_currency_id_foreign` (`currency_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // DEPOSITS, WITHDRAWALS & TRANSACTIONS
        // ====================================================================
        if (!Schema::hasTable('deposits')) {
            DB::unprepared("
                CREATE TABLE `deposits` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `payment_id` varchar(191) DEFAULT NULL,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `amount` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `type` varchar(191) NOT NULL,
                    `proof` varchar(191) DEFAULT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `currency` varchar(50) DEFAULT NULL,
                    `symbol` varchar(50) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `deposits_user_id_foreign` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('withdrawals')) {
            DB::unprepared("
                CREATE TABLE `withdrawals` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `payment_id` varchar(255) DEFAULT NULL,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `amount` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `type` varchar(191) DEFAULT NULL,
                    `proof` varchar(191) DEFAULT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `pix_key` varchar(191) DEFAULT NULL,
                    `pix_type` varchar(191) DEFAULT NULL,
                    `bank_info` text DEFAULT NULL,
                    `currency` varchar(50) DEFAULT NULL,
                    `symbol` varchar(50) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `withdrawals_user_id_foreign` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('transactions')) {
            DB::unprepared("
                CREATE TABLE `transactions` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `payment_id` varchar(100) NOT NULL,
                    `user_id` int(10) unsigned NOT NULL,
                    `payment_method` varchar(191) DEFAULT NULL,
                    `price` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `currency` varchar(20) NOT NULL DEFAULT 'usd',
                    `status` tinyint(4) DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `reference` varchar(191) DEFAULT NULL,
                    `accept_bonus` tinyint(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (`id`),
                    KEY `transactions_user_id_index` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('orders')) {
            DB::unprepared("
                CREATE TABLE `orders` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `session_id` varchar(191) DEFAULT NULL,
                    `transaction_id` varchar(191) DEFAULT NULL,
                    `game` varchar(191) NOT NULL,
                    `game_uuid` varchar(191) NOT NULL,
                    `type` varchar(50) NOT NULL,
                    `type_money` varchar(50) NOT NULL,
                    `amount` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `providers` varchar(191) NOT NULL,
                    `refunded` tinyint(4) NOT NULL DEFAULT 0,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `round_id` varchar(255) DEFAULT NULL,
                    `hash` varchar(255) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `orders_user_id_foreign` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // PAYMENT GATEWAYS
        // ====================================================================
        if (!Schema::hasTable('gateways')) {
            DB::unprepared("
                CREATE TABLE `gateways` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `suitpay_uri` varchar(191) DEFAULT NULL,
                    `suitpay_cliente_id` varchar(191) DEFAULT NULL,
                    `suitpay_cliente_secret` varchar(191) DEFAULT NULL,
                    `stripe_production` tinyint(4) DEFAULT 0,
                    `stripe_public_key` varchar(255) DEFAULT NULL,
                    `stripe_secret_key` varchar(255) DEFAULT NULL,
                    `stripe_webhook_key` varchar(255) DEFAULT NULL,
                    `bspay_uri` varchar(255) DEFAULT NULL,
                    `bspay_cliente_id` varchar(255) DEFAULT NULL,
                    `bspay_cliente_secret` varchar(255) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `public_key` varchar(191) DEFAULT NULL,
                    `private_key` varchar(191) DEFAULT NULL,
                    `mp_client_id` varchar(191) DEFAULT NULL,
                    `mp_client_secret` varchar(191) DEFAULT NULL,
                    `mp_public_key` varchar(191) DEFAULT NULL,
                    `mp_access_token` varchar(191) DEFAULT NULL,
                    `digitopay_uri` varchar(191) DEFAULT NULL,
                    `digitopay_cliente_id` varchar(191) DEFAULT NULL,
                    `digitopay_cliente_secret` varchar(191) DEFAULT NULL,
                    `tribopay_public_key` varchar(191) DEFAULT NULL,
                    `tribopay_api_token` varchar(191) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('suit_pay_payments')) {
            DB::unprepared("
                CREATE TABLE `suit_pay_payments` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `payment_id` varchar(191) DEFAULT NULL,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `withdrawal_id` bigint(20) unsigned NOT NULL,
                    `pix_key` varchar(191) DEFAULT NULL,
                    `pix_type` varchar(50) DEFAULT NULL,
                    `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
                    `observation` text DEFAULT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `suit_pay_payments_user_id_foreign` (`user_id`),
                    KEY `suit_pay_payments_withdrawal_id_foreign` (`withdrawal_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('digito_pay_payments')) {
            DB::unprepared("
                CREATE TABLE `digito_pay_payments` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `payment_id` varchar(191) DEFAULT NULL,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `withdrawal_id` bigint(20) unsigned NOT NULL,
                    `pix_key` varchar(191) NOT NULL,
                    `pix_type` varchar(191) NOT NULL,
                    `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
                    `observation` text DEFAULT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `digito_pay_payments_user_id_foreign` (`user_id`),
                    KEY `digito_pay_payments_withdrawal_id_foreign` (`withdrawal_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('tribo_pay_payments')) {
            DB::unprepared("
                CREATE TABLE `tribo_pay_payments` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `payment_id` varchar(191) DEFAULT NULL,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `withdrawal_id` bigint(20) unsigned DEFAULT NULL,
                    `pix_key` varchar(191) NOT NULL,
                    `pix_type` varchar(191) NOT NULL,
                    `amount` decimal(10,2) NOT NULL,
                    `observation` text DEFAULT NULL,
                    `status` tinyint(1) DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // GAMES & PROVIDERS
        // ====================================================================
        if (!Schema::hasTable('providers')) {
            DB::unprepared("
                CREATE TABLE `providers` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `cover` varchar(255) DEFAULT NULL,
                    `code` varchar(50) DEFAULT NULL,
                    `name` varchar(50) DEFAULT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT 1,
                    `rtp` bigint(20) DEFAULT 90,
                    `views` bigint(20) DEFAULT 1,
                    `distribution` varchar(50) DEFAULT 'play_fiver',
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('categories')) {
            DB::unprepared("
                CREATE TABLE `categories` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(191) NOT NULL,
                    `description` varchar(191) NOT NULL,
                    `image` varchar(191) DEFAULT NULL,
                    `slug` varchar(191) DEFAULT NULL,
                    `url` varchar(191) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `casino_categories_slug_unique` (`slug`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('games')) {
            DB::unprepared("
                CREATE TABLE `games` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `provider_id` int(10) unsigned NOT NULL,
                    `game_server_url` varchar(191) DEFAULT 'inativo',
                    `game_id` varchar(191) NOT NULL,
                    `game_name` varchar(191) NOT NULL,
                    `game_code` varchar(191) NOT NULL,
                    `game_type` varchar(191) DEFAULT NULL,
                    `description` varchar(1000) DEFAULT NULL,
                    `cover` varchar(191) DEFAULT NULL,
                    `status` varchar(191) NOT NULL DEFAULT '0',
                    `technology` varchar(191) DEFAULT 'html5',
                    `has_lobby` tinyint(4) NOT NULL DEFAULT 0,
                    `is_mobile` tinyint(4) NOT NULL DEFAULT 0,
                    `has_freespins` tinyint(4) NOT NULL DEFAULT 0,
                    `has_tables` tinyint(4) NOT NULL DEFAULT 0,
                    `only_demo` tinyint(4) DEFAULT 0,
                    `rtp` bigint(20) NOT NULL DEFAULT 0,
                    `distribution` varchar(191) NOT NULL DEFAULT 'play_fiver',
                    `views` bigint(20) NOT NULL DEFAULT 0,
                    `is_featured` tinyint(4) DEFAULT 0,
                    `show_home` tinyint(4) DEFAULT 1,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `original` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY `games_provider_id_index` (`provider_id`),
                    KEY `games_game_code_index` (`game_code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('category_game')) {
            DB::unprepared("
                CREATE TABLE `category_game` (
                    `category_id` bigint(20) unsigned NOT NULL,
                    `game_id` bigint(20) unsigned NOT NULL,
                    KEY `category_games_category_id_foreign` (`category_id`),
                    KEY `category_games_game_id_foreign` (`game_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('games_keys')) {
            DB::unprepared("
                CREATE TABLE `games_keys` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `merchant_url` varchar(191) DEFAULT NULL,
                    `merchant_id` varchar(191) DEFAULT NULL,
                    `merchant_key` varchar(191) DEFAULT NULL,
                    `agent_code` varchar(255) DEFAULT NULL,
                    `agent_token` varchar(255) DEFAULT NULL,
                    `agent_secret_key` varchar(255) DEFAULT NULL,
                    `api_endpoint` varchar(255) DEFAULT NULL,
                    `salsa_base_uri` varchar(255) DEFAULT NULL,
                    `salsa_pn` varchar(255) DEFAULT NULL,
                    `salsa_key` varchar(255) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `vibra_site_id` varchar(191) DEFAULT NULL,
                    `vibra_game_mode` varchar(191) DEFAULT NULL,
                    `worldslot_agent_code` varchar(191) DEFAULT NULL,
                    `worldslot_agent_token` varchar(191) DEFAULT NULL,
                    `worldslot_agent_secret_key` varchar(191) DEFAULT NULL,
                    `worldslot_api_endpoint` varchar(191) NOT NULL DEFAULT 'https://api.worldslotgame.com/api/v2/',
                    `games2_agent_code` varchar(191) DEFAULT NULL,
                    `games2_agent_token` varchar(191) DEFAULT NULL,
                    `games2_agent_secret_key` varchar(191) DEFAULT NULL,
                    `games2_api_endpoint` varchar(191) NOT NULL DEFAULT 'api.games2api.xyz',
                    `evergame_agent_code` varchar(191) DEFAULT NULL,
                    `evergame_agent_token` varchar(191) DEFAULT NULL,
                    `evergame_api_endpoint` varchar(191) DEFAULT NULL,
                    `venix_agent_code` varchar(191) DEFAULT NULL,
                    `venix_agent_token` varchar(191) DEFAULT NULL,
                    `venix_agent_secret` varchar(191) DEFAULT NULL,
                    `play_gaming_hall` varchar(191) DEFAULT NULL,
                    `play_gaming_key` varchar(191) DEFAULT NULL,
                    `play_gaming_login` varchar(191) DEFAULT NULL,
                    `pig_agent_code` varchar(191) DEFAULT NULL,
                    `pig_agent_token` varchar(191) DEFAULT NULL,
                    `pig_agent_secret` varchar(191) DEFAULT NULL,
                    `playfiver_url` varchar(191) DEFAULT NULL,
                    `playfiver_secret` varchar(191) DEFAULT NULL,
                    `playfiver_code` varchar(191) DEFAULT NULL,
                    `playfiver_token` varchar(191) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('game_favorites')) {
            DB::unprepared("
                CREATE TABLE `game_favorites` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `game_id` bigint(20) unsigned NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `game_favorites_user_id_game_id_unique` (`user_id`,`game_id`),
                    KEY `game_favorites_game_id_foreign` (`game_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('game_likes')) {
            DB::unprepared("
                CREATE TABLE `game_likes` (
                    `user_id` bigint(20) unsigned NOT NULL,
                    `game_id` bigint(20) unsigned NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    UNIQUE KEY `game_likes_user_id_game_id_unique` (`user_id`,`game_id`),
                    KEY `game_likes_game_id_foreign` (`game_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('game_reviews')) {
            DB::unprepared("
                CREATE TABLE `game_reviews` (
                    `user_id` bigint(20) unsigned NOT NULL,
                    `game_id` bigint(20) unsigned NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `description` varchar(191) NOT NULL,
                    `rating` int(11) NOT NULL DEFAULT 0,
                    UNIQUE KEY `game_reviews_user_id_game_id_unique` (`user_id`,`game_id`),
                    KEY `game_reviews_game_id_foreign` (`game_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // GGR (Gross Gaming Revenue)
        // ====================================================================
        if (!Schema::hasTable('ggr_games')) {
            DB::unprepared("
                CREATE TABLE `ggr_games` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL,
                    `provider` varchar(191) NOT NULL,
                    `game` varchar(191) NOT NULL,
                    `balance_bet` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `balance_win` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `currency` varchar(50) DEFAULT NULL,
                    `aggregator` varchar(255) DEFAULT NULL,
                    `type` varchar(20) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ggr_games_fivers_user_id_index` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('ggr_games_world_slots')) {
            DB::unprepared("
                CREATE TABLE `ggr_games_world_slots` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL,
                    `provider` varchar(191) NOT NULL,
                    `game` varchar(191) NOT NULL,
                    `balance_bet` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `balance_win` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `currency` varchar(50) NOT NULL DEFAULT 'BRL',
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ggr_games_world_slots_user_id_index` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // AFFILIATES
        // ====================================================================
        if (!Schema::hasTable('affiliate_histories')) {
            DB::unprepared("
                CREATE TABLE `affiliate_histories` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL,
                    `inviter` int(10) unsigned NOT NULL,
                    `commission` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `commission_type` varchar(191) DEFAULT NULL,
                    `deposited` tinyint(4) DEFAULT 0,
                    `deposited_amount` decimal(10,2) DEFAULT 0.00,
                    `losses` bigint(20) DEFAULT 0,
                    `losses_amount` decimal(10,2) DEFAULT 0.00,
                    `commission_paid` decimal(10,2) DEFAULT 0.00,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `receita` decimal(10,2) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `affiliate_histories_user_id_index` (`user_id`),
                    KEY `affiliate_histories_inviter_index` (`inviter`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('affiliate_withdraws')) {
            DB::unprepared("
                CREATE TABLE `affiliate_withdraws` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `payment_id` varchar(191) DEFAULT NULL,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `amount` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `proof` varchar(191) DEFAULT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `pix_key` varchar(191) DEFAULT NULL,
                    `pix_type` varchar(191) DEFAULT NULL,
                    `type` varchar(50) DEFAULT NULL,
                    `bank_info` text DEFAULT NULL,
                    `currency` varchar(50) DEFAULT NULL,
                    `symbol` varchar(50) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `affiliate_withdraws_user_id_foreign` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('sub_affiliates')) {
            DB::unprepared("
                CREATE TABLE `sub_affiliates` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `affiliate_id` int(10) unsigned NOT NULL,
                    `user_id` int(10) unsigned NOT NULL,
                    `status` tinyint(1) DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `sub_affiliates_affiliate_id_index` (`affiliate_id`),
                    KEY `sub_affiliates_user_id_index` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // SETTINGS & CONFIG
        // ====================================================================
        if (!Schema::hasTable('settings')) {
            DB::unprepared("
                CREATE TABLE `settings` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `software_name` varchar(255) DEFAULT NULL,
                    `software_description` varchar(255) DEFAULT NULL,
                    `software_favicon` varchar(255) DEFAULT NULL,
                    `software_logo_white` varchar(255) DEFAULT NULL,
                    `software_logo_black` varchar(255) DEFAULT NULL,
                    `software_background` varchar(255) DEFAULT NULL,
                    `currency_code` varchar(191) NOT NULL DEFAULT 'BRL',
                    `decimal_format` varchar(20) NOT NULL DEFAULT 'dot',
                    `currency_position` varchar(20) NOT NULL DEFAULT 'left',
                    `revshare_percentage` bigint(20) DEFAULT 20,
                    `ngr_percent` bigint(20) DEFAULT 20,
                    `soccer_percentage` bigint(20) DEFAULT 30,
                    `prefix` varchar(191) NOT NULL DEFAULT 'R\$',
                    `storage` varchar(191) NOT NULL DEFAULT 'local',
                    `initial_bonus` bigint(20) DEFAULT 0,
                    `min_deposit` decimal(10,2) DEFAULT 20.00,
                    `max_deposit` decimal(10,2) DEFAULT 0.00,
                    `min_withdrawal` decimal(10,2) DEFAULT 20.00,
                    `max_withdrawal` decimal(10,2) DEFAULT 0.00,
                    `rollover` bigint(20) DEFAULT 10,
                    `rollover_deposit` bigint(20) DEFAULT 1,
                    `suitpay_is_enable` tinyint(4) DEFAULT 1,
                    `stripe_is_enable` tinyint(4) DEFAULT 1,
                    `bspay_is_enable` tinyint(4) DEFAULT 0,
                    `sharkpay_is_enable` tinyint(4) DEFAULT 1,
                    `tribopay_is_enable` tinyint(1) DEFAULT 0,
                    `turn_on_football` tinyint(4) DEFAULT 1,
                    `revshare_reverse` tinyint(1) DEFAULT 1,
                    `bonus_vip` bigint(20) DEFAULT 100,
                    `activate_vip_bonus` tinyint(1) DEFAULT 1,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `maintenance_mode` tinyint(4) DEFAULT 0,
                    `withdrawal_limit` bigint(20) DEFAULT NULL,
                    `withdrawal_period` varchar(30) DEFAULT NULL,
                    `disable_spin` tinyint(1) NOT NULL DEFAULT 0,
                    `perc_sub_lv1` bigint(20) NOT NULL DEFAULT 4,
                    `perc_sub_lv2` bigint(20) NOT NULL DEFAULT 2,
                    `perc_sub_lv3` bigint(20) NOT NULL DEFAULT 3,
                    `disable_rollover` tinyint(4) DEFAULT 0,
                    `rollover_protection` bigint(20) NOT NULL DEFAULT 1,
                    `cpa_baseline` decimal(10,2) DEFAULT NULL,
                    `cpa_value` decimal(10,2) DEFAULT NULL,
                    `mercadopago_is_enable` tinyint(4) DEFAULT 0,
                    `digitopay_is_enable` tinyint(4) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('setting_mails')) {
            DB::unprepared("
                CREATE TABLE `setting_mails` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `software_smtp_type` varchar(30) DEFAULT NULL,
                    `software_smtp_mail_host` varchar(100) DEFAULT NULL,
                    `software_smtp_mail_port` varchar(30) DEFAULT NULL,
                    `software_smtp_mail_username` varchar(191) DEFAULT NULL,
                    `software_smtp_mail_password` varchar(100) DEFAULT NULL,
                    `software_smtp_mail_encryption` varchar(30) DEFAULT NULL,
                    `software_smtp_mail_from_address` varchar(191) DEFAULT NULL,
                    `software_smtp_mail_from_name` varchar(191) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('custom_layouts')) {
            DB::unprepared("
                CREATE TABLE `custom_layouts` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `font_family_default` varchar(191) DEFAULT NULL,
                    `primary_color` varchar(20) NOT NULL DEFAULT '#0073D2',
                    `primary_opacity_color` varchar(20) DEFAULT NULL,
                    `secundary_color` varchar(20) NOT NULL DEFAULT '#084375',
                    `gray_dark_color` varchar(20) NOT NULL DEFAULT '#3b3b3b',
                    `gray_light_color` varchar(20) NOT NULL DEFAULT '#c9c9c9',
                    `gray_medium_color` varchar(20) NOT NULL DEFAULT '#676767',
                    `gray_over_color` varchar(20) NOT NULL DEFAULT '#1A1C20',
                    `title_color` varchar(20) NOT NULL DEFAULT '#ffffff',
                    `text_color` varchar(20) NOT NULL DEFAULT '#98A7B5',
                    `sub_text_color` varchar(20) NOT NULL DEFAULT '#656E78',
                    `placeholder_color` varchar(20) NOT NULL DEFAULT '#4D565E',
                    `background_color` varchar(20) NOT NULL DEFAULT '#24262B',
                    `background_base` varchar(20) DEFAULT '#ECEFF1',
                    `background_base_dark` varchar(20) DEFAULT '#24262B',
                    `carousel_banners` varchar(20) DEFAULT '#1E2024',
                    `carousel_banners_dark` varchar(20) DEFAULT '#1E2024',
                    `sidebar_color` varchar(20) DEFAULT NULL,
                    `sidebar_color_dark` varchar(20) DEFAULT NULL,
                    `navtop_color` varchar(20) DEFAULT NULL,
                    `navtop_color_dark` varchar(20) DEFAULT NULL,
                    `side_menu` varchar(20) DEFAULT NULL,
                    `side_menu_dark` varchar(20) DEFAULT NULL,
                    `input_primary` varchar(20) DEFAULT NULL,
                    `input_primary_dark` varchar(20) DEFAULT NULL,
                    `footer_color` varchar(20) DEFAULT NULL,
                    `footer_color_dark` varchar(20) DEFAULT NULL,
                    `card_color` varchar(20) DEFAULT NULL,
                    `card_color_dark` varchar(20) DEFAULT NULL,
                    `border_radius` varchar(20) NOT NULL DEFAULT '.25rem',
                    `custom_css` text DEFAULT NULL,
                    `custom_js` text DEFAULT NULL,
                    `custom_header` longtext DEFAULT NULL,
                    `custom_body` longtext DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `instagram` varchar(191) DEFAULT NULL,
                    `facebook` varchar(191) DEFAULT NULL,
                    `telegram` varchar(191) DEFAULT NULL,
                    `twitter` varchar(191) DEFAULT NULL,
                    `whastapp` varchar(191) DEFAULT NULL,
                    `youtube` varchar(191) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // VIP SYSTEM
        // ====================================================================
        if (!Schema::hasTable('vips')) {
            DB::unprepared("
                CREATE TABLE `vips` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `bet_symbol` varchar(255) NOT NULL,
                    `bet_level` bigint(20) NOT NULL DEFAULT 1,
                    `bet_required` bigint(20) DEFAULT NULL,
                    `bet_period` varchar(191) DEFAULT NULL,
                    `bet_bonus` bigint(20) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('vip_users')) {
            DB::unprepared("
                CREATE TABLE `vip_users` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL,
                    `vip_id` int(10) unsigned NOT NULL,
                    `level` bigint(20) NOT NULL,
                    `points` bigint(20) NOT NULL,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `vip_users_user_id_index` (`user_id`),
                    KEY `vip_users_vip_id_index` (`vip_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // MISSIONS
        // ====================================================================
        if (!Schema::hasTable('missions')) {
            DB::unprepared("
                CREATE TABLE `missions` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `challenge_name` varchar(191) NOT NULL,
                    `challenge_description` text NOT NULL,
                    `challenge_rules` text NOT NULL,
                    `challenge_type` varchar(20) NOT NULL DEFAULT 'game',
                    `challenge_link` varchar(191) DEFAULT NULL,
                    `challenge_start_date` datetime NOT NULL,
                    `challenge_end_date` datetime NOT NULL,
                    `challenge_bonus` decimal(20,2) NOT NULL DEFAULT 0.00,
                    `challenge_total` bigint(20) NOT NULL DEFAULT 1,
                    `challenge_currency` varchar(5) NOT NULL,
                    `challenge_provider` varchar(50) DEFAULT NULL,
                    `challenge_gameid` varchar(50) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('mission_users')) {
            DB::unprepared("
                CREATE TABLE `mission_users` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL,
                    `mission_id` int(10) unsigned NOT NULL,
                    `rounds` bigint(20) DEFAULT 0,
                    `rewards` decimal(10,2) DEFAULT 0.00,
                    `status` tinyint(4) NOT NULL DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `mission_users_user_id_index` (`user_id`),
                    KEY `mission_users_mission_id_index` (`mission_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // MISC
        // ====================================================================
        if (!Schema::hasTable('banners')) {
            DB::unprepared("
                CREATE TABLE `banners` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `link` varchar(191) DEFAULT NULL,
                    `image` varchar(191) NOT NULL,
                    `type` varchar(20) NOT NULL DEFAULT 'home',
                    `description` text DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('likes')) {
            DB::unprepared("
                CREATE TABLE `likes` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) unsigned NOT NULL,
                    `liked_user_id` bigint(20) unsigned NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `likes_user_id_foreign` (`user_id`),
                    KEY `likes_liked_user_id_foreign` (`liked_user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('notifications')) {
            DB::unprepared("
                CREATE TABLE `notifications` (
                    `id` char(36) NOT NULL,
                    `type` varchar(191) NOT NULL,
                    `notifiable_type` varchar(191) NOT NULL,
                    `notifiable_id` bigint(20) unsigned NOT NULL,
                    `data` text NOT NULL,
                    `read_at` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('reports')) {
            DB::unprepared("
                CREATE TABLE `reports` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL,
                    `description` varchar(191) NOT NULL,
                    `page_url` varchar(191) NOT NULL,
                    `page_action` varchar(191) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // ====================================================================
        // SPIN (Roleta)
        // ====================================================================
        if (!Schema::hasTable('ggds_spin_config')) {
            DB::unprepared("
                CREATE TABLE `ggds_spin_config` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `prizes` text NOT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('ggds_spin_runs')) {
            DB::unprepared("
                CREATE TABLE `ggds_spin_runs` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `key` varchar(255) NOT NULL,
                    `nonce` varchar(255) NOT NULL,
                    `possibilities` text NOT NULL,
                    `prize` varchar(191) NOT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!Schema::hasTable('websockets_statistics_entries')) {
            DB::unprepared("
                CREATE TABLE `websockets_statistics_entries` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `app_id` varchar(191) NOT NULL,
                    `peak_connection_count` int(11) NOT NULL,
                    `websocket_message_count` int(11) NOT NULL,
                    `api_message_count` int(11) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        // Ordem reversa respeitando foreign keys
        $tables = [
            'websockets_statistics_entries',
            'ggds_spin_runs',
            'ggds_spin_config',
            'reports',
            'notifications',
            'likes',
            'banners',
            'mission_users',
            'missions',
            'vip_users',
            'vips',
            'custom_layouts',
            'setting_mails',
            'sub_affiliates',
            'affiliate_withdraws',
            'affiliate_histories',
            'ggr_games_world_slots',
            'ggr_games',
            'game_reviews',
            'game_likes',
            'game_favorites',
            'games_keys',
            'category_game',
            'games',
            'categories',
            'providers',
            'tribo_pay_payments',
            'digito_pay_payments',
            'suit_pay_payments',
            'gateways',
            'orders',
            'transactions',
            'withdrawals',
            'deposits',
            'currency_alloweds',
            'currencies',
            'system_wallets',
            'wallet_changes',
            'wallets',
            'settings',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'roles',
            'permissions',
            'failed_jobs',
            'personal_access_tokens',
            'password_reset_tokens',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
