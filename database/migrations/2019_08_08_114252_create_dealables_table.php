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
        Schema::create('dealables', function (Blueprint $table) {
            $table->unsignedBigInteger('deal_id');

            $table->foreign('deal_id')
                ->references('id')
                ->on('deals');

            $table->morphs('dealable');

            $table->timestamps();

            $table->primary(
                ['deal_id', 'dealable_id', 'dealable_type'],
                'dealable_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealables');
    }
};
