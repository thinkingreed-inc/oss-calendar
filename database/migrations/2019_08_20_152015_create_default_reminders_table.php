<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefaultRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_reminders', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('calendarlist_id');
            $table->integer('default_reminders_method_id');
            $table->integer('overrides_minutes');
            $table->boolean('is_enable')->default(1)->comment('有効　1:有効  0:無効');
            $table->softDeletes();
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
        Schema::drop('default_reminders');
    }
}
