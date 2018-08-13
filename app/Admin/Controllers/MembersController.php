<?php

namespace App\Admin\Controllers;

use App\Models\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Carbon\Carbon;
use App\Notifications\EmailVerificationNotification;

class MembersController extends Controller
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

            $content->header('用户列表');

            $content->body($this->grid());
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Users');
            $content->description('edit');

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

            $content->header('创建会员');

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
        // 根据回调函数，在页面上用表格的形式展示用户记录
        return Admin::grid(User::class, function (Grid $grid) {
             // 创建一个列名为 ID 的列，内容是用户的 id 字段，并且可以在前端页面点击排序
            $grid->id('ID')->sortable();

             // 创建一个列名为 用户名 的列，内容是用户的 name 字段。下面的 email() 和 created_at() 同理
             $grid->name('用户名')->editable();

             $grid->email('邮箱')->editable();

             $grid->email_verified('已验证邮箱')->display(function ($value) {
                $verify = $value ? 'YES' : 'NO';
                $color = array_get(User::$verifiedColors, $verify, 'grey');

                return "<span class=\"badge bg-$color\">$verify</span>";
            });
            
            $grid->created_at('注册时间');
            // 不在页面显示 `新建` 按钮，因为我们不需要在后台新建用户
            
            // $grid->disableCreateButton();

            $grid->tools(function ($tools) {

                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
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
        return Admin::form(User::class, function (Form $form) {
            $form->tab('Basic', function (Form $form) {
                
                $form->text('name')->rules('required');
                $form->email('email')->rules('required');
                $form->radio('email_verified', '已邮箱验证')->options([true => '是', false => '否']);
                $form->display('created_at');
                $form->display('updated_at');

            })->tab('Profile', function (Form $form) {

                $form->image('avatar', '用户头像');
                $form->mobile('mobile');
                $form->date('birthday');
                $form->display('last_login_ip');
                $form->display('last_login_at');

            })->tab('Password', function (Form $form) {
                $form->password('password', trans('admin.password'))->rules('required|confirmed');

                $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required');
            });
            $form->ignore(['password_confirmation']);
            
            $form->saving(function (Form $form) {
                if (!$form->model()->created_at) {
                    $form->model()->created_at = Carbon::now();
                }
                $form->model()->updated_at = Carbon::now();
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            });

            $form->saved(function(Form $form) {
                if ($form->model()->created_at == $form->model()->updated_at) {
                    $this->afterRegistered($form->model());
                }
            });
        });
    }

    // 创建新用户后需要用户验证邮箱
    public function afterRegistered(User $user)
    {
        // 调用notify方法发送通知
        $user->notify(new EmailVerificationNotification());
    }
}
