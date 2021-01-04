<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvinceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('province', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('province_name', 40)->comment("省份名称");
            $table->string('province_code', 40)->comment("省份编号");
            $table->string('short_name', 50)->comment("简称");
            $table->double('lng', 10, 7)->nullable()->comment("经度");
            $table->double('lat', 10, 7)->comment("纬度");
            $table->integer('sort')->comment("排序字段");
            $table->timestamps();
            $table->comment = "省份表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('province');
    }
}
