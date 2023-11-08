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
        Schema::create('match_data', function (Blueprint $table) {
            $table->id();
            $table->text('access_token')->nullable();
            $table->unsignedInteger('partner_id')->nullable();
            $table->text('offer_uuid')->nullable();
            $table->text('oneTimeToken')->nullable();
            $table->text('co_auth')->nullable();
            $table->text('trading_api_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_data');
    }
};
