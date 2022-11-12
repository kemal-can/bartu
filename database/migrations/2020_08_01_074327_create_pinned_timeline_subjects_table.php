<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinned_timeline_subjects', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            // Using custom INDEX name
            // SQLSTATE[42000]: Syntax error or access violation: 1059 Identifier name 'tbl_pinned_timeline_subjects_timelineable_type_timelineable_id_index' is too long (SQL: alter table `tbl_pinned_timeline_subjects` add index `tbl_pinned_timeline_subjects_timelineable_type_timelineable_id_index`(`timelineable_type`, `timelineable_id`))
            $table->morphs('timelineable', 'timelineable_type_timelineable_id_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pinned_timeline_subjects');
    }
};
