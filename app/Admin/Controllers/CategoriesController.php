<?php

namespace App\Admin\Controllers;

use App\Models\Category;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Tree;
use Encore\Admin\Layout\Row;

class CategoriesController extends Controller
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
            $content->header('分类管理');
            $content->description(trans('admin.list'));
            $content->row(function (Row $row) {
                $row->column(12, $this->treeView()->render());
            });
        });
    }

    /**
    * @return \Encore\Admin\Tree
    */
   protected function treeView()
   {
       return Category::tree(function (Tree $tree) {
           //$tree->disableCreate(); // 禁止新增

           $tree->branch(function ($branch) {
               // NOTE: 可以修改输出内容
               return "{$branch['name']}";
           });
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

            $content->header('header');
            $content->description('description');

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

            $content->header('添加分类');

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
        return Admin::grid(Category::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Category::class, function (Form $form) {

            $form->hidden('id', 'ID');

            $form->select('parent_id', '上级分类')->options(Category::selectOptions(true));

            $form->text('name', '分类名称');
            // 创建一个富文本编辑器
            $form->textarea('description', '分类描述');

            // 创建一组单选框
            $form->radio('is_show', '是否显示')->options(['1' => '是', '0'=> '否'])->default('1');

            $form->text('sort_order', '显示排序')->rules('required|numeric|min:1')->default(50);
        });
    }
}
