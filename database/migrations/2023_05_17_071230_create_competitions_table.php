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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id('competition_id');
            $table->string('mandant_id');
            $table->string('saison_id');
            $table->string('competition_type_id');
            $table->foreign('mandant_id')->references('mandant_id')->on('mandants');
            $table->foreign('saison_id')->references('saison_id')->on('saisons');
            $table->foreign('competition_type_id')->references('competition_type_id')->on('competition_types');
            $table->jsonb('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
