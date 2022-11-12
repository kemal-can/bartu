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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->uuid('uuid');

            $table->string('swatch_color', 7)->nullable();
            $table->unsignedBigInteger('pipeline_id');
            $table->foreign('pipeline_id')
                ->references('id')
                ->on('pipelines');

            $table->unsignedBigInteger('stage_id');
            $table->foreign('stage_id')
                ->references('id')
                ->on('stages');

            $table->unsignedInteger('status')->index()->default(1); // Open
            $table->dateTime('won_date')->index()->nullable();
            $table->dateTime('lost_date')->index()->nullable();
            $table->string('lost_reason')->nullable()->index();

            $table->unsignedBigInteger('user_id')->nullable()->comment('Owner');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->dateTime('owner_assigned_date')->nullable();

            $table->date('expected_close_date')->index()->nullable();
            $table->dateTime('stage_changed_date')->nullable();

            $table->decimal('amount', 15, 3)->index()->nullable();
            $table->unsignedInteger('board_order')
                ->index()
                ->default(0);  // Pushes new deals on board top when sorting by board_order

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->unsignedBigInteger('web_form_id')->nullable();
            $table->foreign('web_form_id')
                ->references('id')
                ->on('web_forms');

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
        Schema::dropIfExists('deals');
    }
};
