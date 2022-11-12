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
        Schema::create('callables', function (Blueprint $table) {
            $table->unsignedBigInteger('call_id');

            $table->foreign('call_id')
                ->references('id')
                ->on('calls');

            $table->morphs('callable');

            $table->primary(
                ['call_id', 'callable_id', 'callable_type'],
                'callable_primary'
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
        Schema::dropIfExists('callables');
    }
};
