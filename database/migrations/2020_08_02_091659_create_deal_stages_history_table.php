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
        Schema::create('deal_stages_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');

            $table->foreign('deal_id')
                ->references('id')
                ->on('deals')
                ->onDelete('cascade');

            $table->unsignedBigInteger('stage_id');

            $table->foreign('stage_id')
                ->references('id')
                ->on('stages')
                ->onDelete('cascade');

            $table->dateTime('entered_at')->index();
            $table->dateTime('left_at')->nullable()->index();
            $table->index(['deal_id', 'stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deal_stages_history');
    }
};
