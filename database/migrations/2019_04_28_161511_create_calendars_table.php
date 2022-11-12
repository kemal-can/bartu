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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();

            $table->string('email');
            $table->string('calendar_id')->index();

            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->unsignedBigInteger('activity_type_id');

            $table->foreign('activity_type_id')
                ->references('id')
                ->on('activity_types');

            $table->text('activity_types');
            $table->text('data')->nullable();

            $table->unsignedBigInteger('access_token_id')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'calendar_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendars');
    }
};
