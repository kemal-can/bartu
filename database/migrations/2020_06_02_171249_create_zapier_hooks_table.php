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
        Schema::create('zapier_hooks', function (Blueprint $table) {
            $table->id();
            $table->string('hook');
            $table->string('resource_name');
            $table->string('action');
            $table->text('data')->nullable();
            $table->unsignedBigInteger('zap_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zapier_hooks');
    }
};
