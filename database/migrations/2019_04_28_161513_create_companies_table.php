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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');

            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->comment('Owner');

            $table->dateTime('owner_assigned_date')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreign('source_id')
                ->references('id')
                ->on('sources');

            $table->unsignedBigInteger('industry_id')->nullable();
            $table->foreign('industry_id')
                ->references('id')
                ->on('industries');

            $table->unsignedBigInteger('parent_company_id')
                ->nullable();

            $table->foreign('parent_company_id')
                ->references('id')
                ->on('companies');

            $table->string('name');

            $table->string('email')->nullable();

            $table->string('domain')->nullable();

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
        Schema::dropIfExists('companies');
    }
};
