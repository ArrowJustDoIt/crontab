<?php

namespace ArrowJustDoIt\Crontab\Http\Controllers;

use ArrowJustDoIt\Crontab\Http\Models\CrontabLog;
use Illuminate\Routing\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CrontabLogController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $content->breadcrumb(
            ['text' => '定时任务日志', 'url' => '/crontabLogs'],
            ['text' => '列表']
        );
        return $content
            ->header('列表')
            ->description('定时任务日志')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        $content->breadcrumb(
            ['text' => '定时任务日志', 'url' => '/crontabLogs'],
            ['text' => '详情']
        );
        return $content
            ->header('详情')
            ->description('定时任务日志')
            ->body($this->detail($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CrontabLog());
        $grid->disableCreateButton();
        $grid->id('Id')->sortable();
        $grid->type('类型')->using(CrontabController::CRONTAB_TYPE)->label('default');
        $grid->title('任务标题');
        $grid->created_at('执行时间');
        $grid->status('状态')->sortable()->using(['0'=>'失败','1'=>'成功'])->display(function ($status) {
            if($status == '失败'){
                return '<span class="label label-danger">'.$status.'</span>';
            }else{
                return '<span class="label label-success">'.$status.'</span>';
            }
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('title', '任务标题');
            $filter->equal('type', '类型')->select(CrontabController::CRONTAB_TYPE);

        });
        return $grid;
    }


    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CrontabLog::findOrFail($id));

        $show->type('类型')->using(CrontabController::CRONTAB_TYPE)->label();
        $show->cid('任务ID');
        $show->title('任务标题');
        $show->created_at('执行时间');
        $show->status('状态')->using([0 => '失败',1 => '成功']);
        $show->remark('执行结果');

        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CrontabLog);
        return $form;
    }
}
