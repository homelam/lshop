<?php

namespace App\Admin\Controllers;

use App\Models\Brand;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class BrandsController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('品牌列表');
            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑品牌');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('新建品牌');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Brand::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('品牌名称');
            $grid->logo()->display(function($logo) {
                return "<img src=".getImageUrlAttribute($logo)." width='30px'/>";
            });

            $grid->site_url('官网');
            $grid->sort_order('显示排序')->sortable();
            $grid->is_show('是否显示')->display(function ($value) {
                return $value ? '是' : '否';
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Brand::class, function (Form $form) {

            // 创建一个输入框，第一个参数 name 是模型的字段名，第二个参数是该字段描述
            $form->text('name', '品牌名称')->rules('required');
            // 创建一个选择图片的框
            $form->image('logo', 'LOGO')->move('brands')->rules('required|image');
            // 官网
            $form->url('site_url', '官方网址')->rules('required|url');
            // 创建一个富文本编辑器
            $form->textarea('description', '品牌描述')->rules('required');
            // 创建一组单选框
            $form->radio('is_show', '是否显示')->options(['1' => '是', '0'=> '否'])->default('1');

            $form->text('sort_order', '显示排序')->rules('required|numeric|min:1')->default(50);
        });
    }
}
