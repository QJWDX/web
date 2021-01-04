<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('uid', 40)->comment("文件uid");
            $table->enum('type', ['image','voice','video','annex','file'])->comment("文件类型");
            $table->string('disks', 50)->comment("文件磁盘");
            $table->string('path')->nullable()->comment("文件路径");
            $table->string('mime_type')->comment("文件mimeType");
            $table->string('md5')->comment("Md5");
            $table->string('title')->comment("文件标题");
            $table->string('folder', 50)->comment("存储文件夹");
            $table->integer('size')->comment("文件大小");
            $table->smallInteger('width')->default(0)->comment("宽度");
            $table->smallInteger('height')->default(0)->comment("高度");
            $table->mediumInteger('downloads')->default(0)->comment("下载次数");
            $table->tinyInteger('public')->default(0)->comment("是否公开0否1是");
            $table->tinyInteger('editor')->default(0)->comment("富编辑器图片0否1是");
            $table->tinyInteger('status')->default(0)->comment("状态");
            $table->integer('user_id')->default(0)->comment("创建用户");
            $table->timestamps();
            $table->softDeletes();
            $table->comment = "文件表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
