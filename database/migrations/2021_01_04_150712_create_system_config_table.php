<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_config', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('system_name')->comment("系统名称");
            $table->string('system_url')->comment("系统地址");
            $table->string('system_logo')->nullable()->comment("系统logo");
            $table->string('system_version')->comment("系统版本");
            $table->string('system_icp')->nullable()->comment("备案号");
            $table->string('system_copyright')->nullable()->comment("版权");
            $table->string('system_watermark')->nullable()->comment("系统水印");
            $table->string('technical_support')->nullable()->comment("技术支持");
            $table->string('system_remark')->nullable()->comment("备注");
            $table->timestamps();
            $table->comment = "系统配置表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_config');
    }
}
