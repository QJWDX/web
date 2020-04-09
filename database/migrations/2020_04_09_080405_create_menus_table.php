<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->comment('父级菜单id');
            $table->string('name')->comment('菜单名称');
            $table->string('icon')->comment('element图标，例如el-icon-lx-home');
            $table->string('path')->comment('父级菜单id');
            $table->string('component')->comment('前端组件地址');
            $table->tinyInteger('is_auth')->default(1)->comment('是否要验证（默认需要）（1-是，0-否）');
            $table->tinyInteger('is_show')->default(1)->comment('是否展示（默认是）(1-是，0-否)');
            $table->tinyInteger('is_default')->default(0)->comment('是否为默认路由，即系统的首页(1-是，0-否)');
            $table->tinyInteger('sort_field')->default(0)->comment('排序');
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
        Schema::dropIfExists('menus');
    }
}
