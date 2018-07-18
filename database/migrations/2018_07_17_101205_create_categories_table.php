<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('分类名称')->index('cat_name');
            $table->mediumInteger('parent_id')->default('0')->comment('分类父级id')->index('parent_id');
            $table->text('description')->comment('分类描述');
            $table->unsignedTinyInteger('is_show')->default(1)->comment('是否显示');
            $table->mediumInteger('sort_order')->default(50)->comment('分类显示排序,值越大，权重越高');
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
        Schema::dropIfExists('categories');
    }
}
