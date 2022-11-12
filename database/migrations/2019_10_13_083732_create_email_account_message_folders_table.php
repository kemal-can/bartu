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
        Schema::create('email_account_message_folders', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');
            $table->foreign('message_id')
                ->references('id')
                ->on('email_account_messages')
                ->onDelete('cascade');

            $table->unsignedBigInteger('folder_id');
            $table->foreign('folder_id')
                ->references('id')
                ->on('email_account_folders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_account_message_folders');
    }
};
