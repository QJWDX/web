<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->default(0)->comment("用户id");
            $table->string('path')->comment("路由url");
            $table->enum("method", ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'])->comment("http请求方式");
            $table->string('ip', 50)->nullable()->comment("请求ip");
            $table->text('input')->nullable()->comment("请求参数");
            $table->text('sql')->nullable()->comment("sql");
            $table->timestamps();
            $table->comment = "操作日志表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operation_log');
    }
}
