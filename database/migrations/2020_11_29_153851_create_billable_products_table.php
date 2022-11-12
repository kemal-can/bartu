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
        Schema::create('billable_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 15, 3)->default(0);
            $table->decimal('qty', 15, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('tax_rate', 15, 3)->default(0);
            $table->string('tax_label');
            $table->string('discount_type')->nullable();
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('amount', 15, 3)->index()->default(0);

            $table->text('note')->nullable();
            $table->integer('display_order')->index();

            $table->unsignedBigInteger('billable_id');
            $table->foreign('billable_id')
                ->references('id')
                ->on('billables');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')
                ->references('id')
                ->on('products');

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
        Schema::dropIfExists('billable_products');
    }
};
