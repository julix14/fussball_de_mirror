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
        Schema::create('leagues', function (Blueprint $table) {
            $table->string('league_id')->primary();
            $table->string('competition_id');
            $table->string('game_class_id');
            $table->string('area_id');
            $table->string('team_kind_id');
            $table->foreign('competition_id')->references('competition_id')->on('competitions');
            $table->foreign('game_class_id')->references('game_class_id')->on('game_classes');
            $table->foreign('area_id')->references('area_id')->on('areas');
            $table->foreign('team_kind_id')->references('team_kind_id')->on('team_kinds');
            $table->jsonb('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leagues');
    }
};
