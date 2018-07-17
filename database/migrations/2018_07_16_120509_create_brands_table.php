<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60)->default('')->comment('品牌名称')->index('cat_name');
            $table->string('logo', 150)->default('')->comment('品牌LOGO');
            $table->string('description', 255)->default('')->comment('品牌描述');
            $table->string('site_url', 150)->default('')->comment('品牌官网');
            $table->mediumInteger('sort_order')->default(50)->comment('品牌显示排序,值越大，权重越高')->index('sort_order');
            $table->boolean('is_show')->default(true)->comment('是否显示');
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
        Schema::dropIfExists('brands');
    }
}
