<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('condition')->nullable();
            $table->integer('condition_step')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn('condition');
            $table->dropColumn('condition_step');
        });
    }
};
