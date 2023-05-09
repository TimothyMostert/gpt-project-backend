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
        Schema::create('prompt_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->constrained();
            $table->boolean('formatted')->default(false);
            $table->json('response')->nullable();
            $table->longText('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_responses');
    }
};
