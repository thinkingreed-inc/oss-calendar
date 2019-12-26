<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique()->comment('ログインID');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('lastname', 500)->nullable();
            $table->string('firstname', 500)->nullable();
            $table->integer('home_page_id')->default(1);
            $table->integer('calendarlist_id')->default(1);
            $table->integer('setting_id')->default(1);
            $table->integer('default_department_id')->default(1);
            $table->boolean('is_enable')->default(1)->comment('有効　1:有効  0:無効');
            $table->integer('role_id')->default(1)->comment('権限　1:admin  2:一般');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
