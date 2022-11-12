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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');

            $table->text('description')->nullable();
            $table->text('note')->nullable();

            $table->date('due_date')->index();
            $table->time('due_time')->nullable()->index();

            $table->time('end_time')->nullable()->index();
            $table->date('end_date')->index();

            $table->unsignedInteger('reminder_minutes_before')->nullable();
            $table->dateTime('reminder_at')->index()->nullable();
            $table->dateTime('reminded_at')->nullable();

            $table->unsignedBigInteger('user_id')->comment('Owner');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->dateTime('owner_assigned_date')->nullable();

            $table->unsignedBigInteger('activity_type_id');
            $table->foreign('activity_type_id')
                ->references('id')
                ->on('activity_types');

            $table->dateTime('completed_at')->index()->nullable();

            $table->unsignedBigInteger('created_by');

            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->softDeletes();
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
        Schema::dropIfExists('activities');
    }
};
