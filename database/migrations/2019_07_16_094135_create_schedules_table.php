<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('calendarlist_id')->unsigned()->comment('カレンダーリストID');
            $table->string('calendarlists_calendar_id',500)->nullable();
            $table->string('kind',500)->nullable();
            $table->string('etag',500)->nullable();
            $table->string('event_id',500)->nullable();
            $table->integer('status_id')->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('summary',500)->nullable();
            $table->string('description',500)->nullable();
            $table->string('location',500)->nullable();
            $table->string('color_id',500)->nullable();
            $table->json('creator')->nullable();
            $table->integer('organizer_id')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('end_time_unspecified')->nullable();
            $table->string('recurring_event_id',500)->nullable();
            $table->integer('original_start_time_id')->nullable();
            $table->string('transparency',500)->nullable();
            $table->string('visibility',500)->nullable();
            $table->string('ical_uid',500)->nullable();
            $table->integer('sequence')->nullable();
            $table->boolean('attendees_omitted')->nullable();
            $table->json('extended_properties_id')->nullable();
            $table->string('hangout_link',500)->nullable();
            $table->json('gadget')->nullable();
            $table->boolean('anyone_can_add_self')->nullable();
            $table->boolean('guests_can_invite_others')->nullable();
            $table->boolean('guests_can_modify')->nullable();
            $table->boolean('guests_can_see_other_guests')->nullable();
            $table->boolean('private_copy')->nullable();
            $table->boolean('locked')->nullable();
            $table->boolean('reminders_use_default')->nullable();
            $table->json('source')->nullable();
            $table->integer('visibility_id')->nullable();
            $table->integer('public_setting_id')->nullable();
            $table->integer('event_type_id')->unsigned()->nullable();
            $table->boolean('allday')->nullable();
            $table->timestamps();

            $table->foreign('calendarlist_id')
                ->references('id')
                ->on('calendarlists')
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
        Schema::dropIfExists('schedules');
    }
}
