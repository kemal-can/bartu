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
        Schema::create('billables', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tax_type')->default(1);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->morphs('billableable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billables');
    }
};
