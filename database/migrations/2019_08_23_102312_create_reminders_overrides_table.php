<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemindersOverridesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders_overrides', function(Blueprint $table) {
            $table->increments('id');           
            $table->integer('schedule_id');
            $table->integer('user_id');
            $table->integer('overrides_method_id')->nullable();
            $table->integer('overrides_minutes')->nullable();
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
        Schema::drop('reminders_overrides');
    }
}
