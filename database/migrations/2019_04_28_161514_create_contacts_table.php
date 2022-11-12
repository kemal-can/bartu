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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();

            $table->uuid('uuid');

            $table->unsignedBigInteger('user_id')->nullable()->comment('Owner');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->dateTime('owner_assigned_date')->nullable();

            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreign('source_id')
                ->references('id')
                ->on('sources');

            $table->string('first_name');
            $table->string('last_name')->nullable();

            $table->string('job_title')->nullable();

            $table->string('avatar')->nullable();

            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();

            $table->unsignedInteger('country_id')->nullable();

            $table->foreign('country_id')
                ->references('id')
                ->on(\Config::get('countries.table_name'));

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->unsignedBigInteger('next_activity_id')->nullable();

            $table->foreign('next_activity_id')
                ->references('id')
                ->on('activities');
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
        Schema::dropIfExists('contacts');
    }
};
