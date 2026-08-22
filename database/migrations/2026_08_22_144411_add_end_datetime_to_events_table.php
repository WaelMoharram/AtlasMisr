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
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedTinyInteger('event_hour')->nullable()->after('event_day');
            $table->unsignedTinyInteger('event_minute')->nullable()->after('event_hour');

            $table->integer('ends_year')->nullable()->after('date_precision');
            $table->unsignedTinyInteger('ends_month')->nullable()->after('ends_year');
            $table->unsignedTinyInteger('ends_day')->nullable()->after('ends_month');
            $table->unsignedTinyInteger('ends_hour')->nullable()->after('ends_day');
            $table->unsignedTinyInteger('ends_minute')->nullable()->after('ends_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'event_hour',
                'event_minute',
                'ends_year',
                'ends_month',
                'ends_day',
                'ends_hour',
                'ends_minute',
            ]);
        });
    }
};
