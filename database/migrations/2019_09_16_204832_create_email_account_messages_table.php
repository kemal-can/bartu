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
        Schema::create('email_account_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_account_id');

            $table->foreign('email_account_id')
                ->references('id')
                ->on('email_accounts');

            $table->string('remote_id')->index()->comment('Remote Message Identifier (uid, id)');
            $table->string('message_id', 256)->index()->nullable()->comment('Internet Message ID');

            $table->string('subject', 256)->nullable();
            $table->mediumText('html_body')->nullable();
            $table->mediumText('text_body')->nullable();

            $table->boolean('is_draft')->default(false);
            $table->boolean('is_read')->index()->default(true);
            $table->boolean('is_sent_via_app')->default(true);

            $table->dateTime('date');

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
        Schema::dropIfExists('email_account_messages');
    }
};
