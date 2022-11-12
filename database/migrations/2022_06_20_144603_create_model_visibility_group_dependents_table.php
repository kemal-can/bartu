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
        Schema::create('model_visibility_group_dependents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_visibility_group_id');
            $table->foreign('model_visibility_group_id', 'visibility_group_id_foreign')
                ->references('id')
                ->on('model_visibility_groups')
                ->onDelete('cascade');
            $table->morphs('dependable', 'dependable_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_visibility_group_dependents');
    }
};
