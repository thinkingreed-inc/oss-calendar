<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndividualGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('individual_groups', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name',500);
            $table->integer('rank');
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
        Schema::drop('individual_groups');
    }
}
