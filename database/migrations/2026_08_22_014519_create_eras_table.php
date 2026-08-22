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
        Schema::create('eras', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->integer('start_year');
            $table->integer('end_year');
            $table->text('description')->nullable();
            $table->json('boundary_geojson')->nullable();
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
        Schema::dropIfExists('eras');
    }
};
