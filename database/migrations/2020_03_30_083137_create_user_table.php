<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->string('tel');
            $table->string('id_card');
            $table->tinyInteger('sex')->default(0);
            $table->string('address')->nullable();
            $table->string('head_img_url')->nullable();
            $table->dateTime('last_login_time')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->integer('login_count')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_super')->default(0);
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
