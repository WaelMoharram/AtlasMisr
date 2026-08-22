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
        Schema::create('event_frames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->integer('occurs_year');
            $table->unsignedTinyInteger('occurs_month')->nullable();
            $table->unsignedTinyInteger('occurs_day')->nullable();
            $table->unsignedTinyInteger('occurs_hour')->nullable();
            $table->unsignedTinyInteger('occurs_minute')->nullable();

            $table->enum('geometry_type', ['point', 'polygon'])->default('point');
            $table->json('geometry');
            $table->string('icon')->nullable();
            $table->string('label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_frames');
    }
};
