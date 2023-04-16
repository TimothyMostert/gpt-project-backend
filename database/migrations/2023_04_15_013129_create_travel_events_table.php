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
        Schema::create('travel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('travel_mode_id')->constrained();
            $table->unsignedBigInteger('origin_location_id')->nullable();
            $table->foreign('origin_location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('destination_location_id')->nullable();
            $table->foreign('destination_location_id')->references('id')->on('locations');
            $table->string('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_events');
    }
};
