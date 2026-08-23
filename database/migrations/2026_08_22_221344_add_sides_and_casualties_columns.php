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
            // Labels for the two sides in an animated troop-movement
            // sequence (e.g. "مصر" / "إسرائيل"). Null on events that don't
            // use side-based frames — they keep using plain point/polygon
            // frames as before.
            $table->string('side_a_label')->nullable()->after('video_url');
            $table->string('side_b_label')->nullable()->after('side_a_label');
        });

        Schema::table('event_frames', function (Blueprint $table) {
            $table->enum('side', ['a', 'b'])->nullable()->after('event_id');
            $table->unsignedInteger('troop_count')->nullable()->after('label');
            $table->unsignedInteger('casualties')->nullable()->after('troop_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['side_a_label', 'side_b_label']);
        });

        Schema::table('event_frames', function (Blueprint $table) {
            $table->dropColumn(['side', 'troop_count', 'casualties']);
        });
    }
};
