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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('era_id')->constrained()->cascadeOnDelete();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->integer('event_year');
            $table->unsignedTinyInteger('event_month')->nullable();
            $table->unsignedTinyInteger('event_day')->nullable();
            $table->enum('date_precision', ['exact', 'approximate', 'century'])->default('approximate');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_name')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->json('media')->nullable();
            $table->foreignId('source_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'reviewed', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
