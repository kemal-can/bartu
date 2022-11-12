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
        Schema::create('activityables', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id');

            $table->foreign('activity_id')
                ->references('id')
                ->on('activities');

            $table->morphs('activityable');

            $table->primary(
                ['activity_id', 'activityable_id', 'activityable_type'],
                'activityable_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activityables');
    }
};
