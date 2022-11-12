<?php

use App\Enums\SyncState;
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
        Schema::create('synchronizations', function (Blueprint $table) {
            $table->string('id');

            // Relationships.
            $table->morphs('synchronizable');

            // Data.
            $table->string('token')->nullable();
            $table->string('resource_id')->nullable();

            // Timestamps.
            $table->datetime('expires_at')->nullable();
            $table->datetime('last_synchronized_at');
            $table->datetime('start_sync_from');

            $table->string('sync_state', 30)->default(SyncState::ENABLED->value);
            $table->text('sync_state_comment')->nullable();

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
        Schema::dropIfExists('synchronizations');
    }
};
