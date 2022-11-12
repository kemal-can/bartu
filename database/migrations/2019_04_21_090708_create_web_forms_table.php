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
        Schema::create('web_forms', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->boolean('status');
            $table->uuid('uuid');
            $table->string('title_prefix')->nullable();

            $table->text('sections');
            $table->text('notifications')->nullable();
            $table->text('styles');
            $table->text('submit_data');
            $table->unsignedInteger('total_submissions')->default(0);

            $table->string('locale');

            $table->unsignedBigInteger('user_id')->comment('Owner');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users');

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
        Schema::dropIfExists('web_forms');
    }
};
