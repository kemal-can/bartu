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
        Schema::create('email_account_message_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');

            $table->foreign('message_id')
                ->references('id')
                ->on('email_account_messages')
                ->onDelete('cascade');

            $table->string('name');
            $table->text('value')->nullable();

            $table->string('header_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_account_message_headers');
    }
};
