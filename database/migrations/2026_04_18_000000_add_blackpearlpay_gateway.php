<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('gateways', 'blackpearlpay_api_token')) {
                $table->string('blackpearlpay_api_token')->nullable()->after('veopag_client_secret');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'blackpearlpay_is_enable')) {
                $table->boolean('blackpearlpay_is_enable')->default(false)->after('veopag_is_enable');
            }
        });

        if (!Schema::hasTable('blackpearlpay_payments')) {
            Schema::create('blackpearlpay_payments', function (Blueprint $table) {
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
            if (Schema::hasColumn('gateways', 'blackpearlpay_api_token')) {
                $table->dropColumn('blackpearlpay_api_token');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'blackpearlpay_is_enable')) {
                $table->dropColumn('blackpearlpay_is_enable');
            }
        });

        Schema::dropIfExists('blackpearlpay_payments');
    }
};