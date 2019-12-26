<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalendarlistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendarlists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('ユーザID');
            $table->string('kind',500)->nullable();
            $table->string('etag',500)->nullable();
            $table->string('summary',500)->nullable();
            $table->string('description',500)->nullable();
            $table->string('location',500)->nullable();
            $table->string('time_zone',500)->nullable();
            $table->string('summary_override',500)->nullable();
            $table->string('color_id',500)->nullable();
            $table->string('background_color',500)->nullable();
            $table->string('foreground_color',500)->nullable();
            $table->boolean('hidden')->nullable();
            $table->boolean('selected')->nullable();
            $table->string('access_role',500)->nullable();
            $table->boolean('primary')->nullable();
            $table->boolean('deleted')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('calendarlists');
    }
}
