<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** English versions of the Arabic content fields, so the whole site can be read in either language. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eras', function (Blueprint $table) {
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('location_name_en')->nullable()->after('location_name');
            $table->string('category_en')->nullable()->after('category');
            $table->text('description_en')->nullable()->after('description');
            $table->string('side_a_label_en')->nullable()->after('side_a_label');
            $table->string('side_b_label_en')->nullable()->after('side_b_label');
        });

        Schema::table('event_frames', function (Blueprint $table) {
            $table->string('label_en')->nullable()->after('label');
        });

        Schema::table('sources', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('reliability_note_en')->nullable()->after('reliability_note');
        });
    }

    public function down(): void
    {
        Schema::table('eras', fn (Blueprint $t) => $t->dropColumn('description_en'));
        Schema::table('events', fn (Blueprint $t) => $t->dropColumn(['location_name_en', 'category_en', 'description_en', 'side_a_label_en', 'side_b_label_en']));
        Schema::table('event_frames', fn (Blueprint $t) => $t->dropColumn('label_en'));
        Schema::table('sources', fn (Blueprint $t) => $t->dropColumn(['title_en', 'reliability_note_en']));
    }
};
