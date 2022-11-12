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
        Schema::create('noteables', function (Blueprint $table) {
            $table->unsignedBigInteger('note_id');

            $table->foreign('note_id')
                ->references('id')
                ->on('notes');

            $table->morphs('noteable');

            $table->primary(
                ['note_id', 'noteable_id', 'noteable_type'],
                'noteable_primary'
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
        Schema::dropIfExists('noteables');
    }
};
