<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->string('veopag_client_id')->nullable()->after('tribopay_api_token');
            $table->string('veopag_client_secret')->nullable()->after('veopag_client_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('veopag_is_enable')->default(true)->after('tribopay_is_enable');
        });

        // Create veo_pag_payments table for withdrawal tracking
        Schema::create('veo_pag_payments', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->dropColumn(['veopag_client_id', 'veopag_client_secret']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('veopag_is_enable');
        });

        Schema::dropIfExists('veo_pag_payments');
    }
};
