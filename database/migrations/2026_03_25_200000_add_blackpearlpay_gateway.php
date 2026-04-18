<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('gateways', 'blackpearlpay_uri')) {
                $table->string('blackpearlpay_uri')->nullable()->after('veopag_client_secret');
            }

            if (!Schema::hasColumn('gateways', 'blackpearlpay_api_token')) {
                $table->string('blackpearlpay_api_token')->nullable()->after('blackpearlpay_uri');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'blackpearlpay_is_enable')) {
                $table->boolean('blackpearlpay_is_enable')->default(true)->after('veopag_is_enable');
            }
        });

        if (!Schema::hasTable('black_pearl_pay_payments')) {
            Schema::create('black_pearl_pay_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('withdrawal_id')->nullable();
                $table->string('pix_key')->nullable();
                $table->string('pix_type')->nullable();
                $table->decimal('amount', 20, 2)->default(0);
                $table->string('observation')->nullable();
                $table->integer('status')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            if (Schema::hasColumn('gateways', 'blackpearlpay_uri')) {
                $table->dropColumn('blackpearlpay_uri');
            }

            if (Schema::hasColumn('gateways', 'blackpearlpay_api_token')) {
                $table->dropColumn('blackpearlpay_api_token');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'blackpearlpay_is_enable')) {
                $table->dropColumn('blackpearlpay_is_enable');
            }
        });

        Schema::dropIfExists('black_pearl_pay_payments');
    }
};
