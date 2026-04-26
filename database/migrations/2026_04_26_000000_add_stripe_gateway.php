<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->string('stripe_public_key')->nullable()->after('blackpearlpay_api_token');
            $table->string('stripe_secret_key')->nullable()->after('stripe_public_key');
            $table->string('stripe_webhook_secret')->nullable()->after('stripe_secret_key');
            $table->string('stripe_connected_account_id')->nullable()->after('stripe_webhook_secret');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('stripe_is_enable')->default(false)->after('blackpearlpay_is_enable');
        });

        Schema::create('stripe_payments', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 20, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_public_key',
                'stripe_secret_key',
                'stripe_webhook_secret',
                'stripe_connected_account_id',
            ]);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('stripe_is_enable');
        });

        Schema::dropIfExists('stripe_payments');
    }
};