<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * @var string
     */
    protected $tableName;

    /**
     * Initialize new class instance.
     */
    public function __construct()
    {
        $this->tableName = \Config::get('settings.drivers.database.options.table', 'settings');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            // $table->timestamps();

            $table->unique(['user_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
};
