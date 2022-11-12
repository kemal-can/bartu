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
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->unsignedInteger('win_probability')->default(0);
            $table->unsignedInteger('display_order')->index()->nullable();

            $table->unsignedBigInteger('pipeline_id');

            $table->foreign('pipeline_id')
                ->references('id')
                ->on('pipelines');

            $table->timestamps();

            $table->unique(['name', 'pipeline_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stages');
    }
};
