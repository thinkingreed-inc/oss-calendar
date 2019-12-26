<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendees', function(Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('schedule_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('attendee_id',500)->nullable();
            $table->string('email',500)->nullable();
            $table->string('display_name',500)->nullable();
            $table->boolean('organizer')->nullable();
            $table->boolean('self')->nullable();
            $table->boolean('resource')->nullable();
            $table->boolean('optional')->nullable();
            $table->integer('response_status_id')->nullable();
            $table->string('comment',500)->nullable();
            $table->integer('additional_guests')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('schedule_id')
                ->references('id')
                ->on('schedules')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attendees');
    }
}
