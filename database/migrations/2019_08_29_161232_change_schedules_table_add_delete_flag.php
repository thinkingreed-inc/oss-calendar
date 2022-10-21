<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSchedulesTableAddDeleteFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //deleteフラグを追加
        Schema::table('schedules', function ($table) {
            $table->boolean('recurring')->default(0);
            $table->integer('recurring_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('parent_uid')->nullable();
            $table->boolean('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('recurring');
            $table->dropColumn('recurring_id');
            $table->dropColumn('parent_id');
            $table->dropColumn('parent_uid');
            $table->dropColumn('deleted');
        });
    }
}
