<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBelongIndividualGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('belong_individual_groups', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('individual_group_id');
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
        Schema::drop('belong_individual_groups');
    }
}
