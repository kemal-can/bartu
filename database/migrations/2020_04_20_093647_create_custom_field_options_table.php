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
        Schema::create('custom_field_options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('custom_field_id');
            $table->foreign('custom_field_id')
                ->references('id')
                ->on('custom_fields')
                ->onDelete('cascade');

            $table->string('name');

            $table->unique(['custom_field_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_field_options');
    }
};
