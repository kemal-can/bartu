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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('timezone');
            $table->string('date_format');
            $table->string('time_format');
            $table->string('locale', 12)->default('en');
            $table->unsignedInteger('first_day_of_week')->default(0);
            $table->text('mail_signature')->nullable();
            $table->dateTime('last_active_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->boolean('super_admin')->default(false);
            $table->boolean('access_api')->default(false);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};