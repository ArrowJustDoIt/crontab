<?php

namespace ArrowJustDoIt\Crontab\Http\Controllers;

use Cron\CronExpression;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use ArrowJustDoIt\Crontab\Http\Models\Crontab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;

class CrontabController extends Controller
{
    use HasResourceActions;

    const CRONTAB_TYPE = [
        'sql'=>'执行sql',
        'shell'=>'执行shell',
        'url'=>'请求url'
    ];
    const CRONTAB_STATUS = [
        'normal'=>'正常',
        'disable'=>'禁用',
        'completed'=>'完成',
        'expired'=>'过期'
    ];

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $content->breadcrumb(
            ['text' => '定时任务', 'url' => '/crontabs'],
            ['text' => '列表']
        );
        return $content
            ->header('列表')
            ->description('定时任务')
            ->body($this->grid());
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $content->breadcrumb(
            ['text' => '定时任务', 'url' => '/crontabs'],
            ['text' => '编辑']
        );
        return $content
            ->header('编辑')
            ->description('定时任务')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        $content->breadcrumb(
            ['text' => '定时任务', 'url' => '/crontabs'],
            ['text' => '创建']
        );
        return $content
            ->header('创建')
            ->description('定时任务')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Crontab);
        $grid->id('Id')->sortable();
        $grid->type('类型')->using(self::CRONTAB_TYPE)->label('default');
        $grid->title('任务标题');
        $grid->maximums('最大次数');
        $grid->executes('已执行次数')->sortable();
        $grid->execute_at('下次预计时间');
        $grid->end_at('最后执行时间')->sortable();
        $grid->status('状态')->sortable()->using(self::CRONTAB_STATUS)->display(function ($status) {
            switch ($status){
                case '正常':
                    return '<span class="label label-success">'.$status.'</span>';
                    break;
                case '禁用':
                    return '<span class="label label-danger">'.$status.'</span>';
                    break;
                case '完成':
                    return '<span class="label label-info">'.$status.'</span>';
                    break;
                default :
                    return '<span class="label label-warning">'.$status.'</span>';
                    break;
            }
        });
        $grid->created_at('创建时间');
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('title', '任务标题');
            $filter->equal('type', '类型')->select(self::CRONTAB_TYPE);

        });

        return $grid;
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Crontab);

        $form->text('title', '任务标题')->rules('required',['required'=>'任务标题不能为空']);
        $form->select('type','任务类型')->options(self::CRONTAB_TYPE)->help("1. URL类型是完整的URL地址，如： <code>http://www.baidu.com/</code> ；<br>2. 如果你的服务器 php.ini 未开启 <code>shell_exec()</code> 函数，则不能使用URL类型和Shell类型模式！")->rules('required|in:url,sql,shell',['required'=>'任务类型不能为空','in'=>'参数错误']);
        $form->textarea('contents', '内容')->rows(3)->rules('required',['required'=>'内容不能为空']);
        $form->text('schedule', '执行周期')->default('* * * * *')->help("请使用<code>Cron</code>表达式")->rules(function ($form) {
            $value = $form->model()->schedule;
            if (empty($value)){
                return 'required';
            }
            if (!CronExpression::isValidExpression($value)){
//                return 'max:0';
            }
        },['required'=>'执行周期不能为空','max'=>'执行周期 Cron 表达式错误']);

        $form->html("<pre><code>*    *    *    *    *
-    -    -    -    -
|    |    |    |    +--- day of week (0 - 7) (Sunday=0 or 7)
|    |    |    +-------- month (1 - 12)
|    |    +------------- day of month (1 - 31)
|    +------------------ hour (0 - 23)
+----------------------- min (0 - 59)</code></pre>");
        $checkSchedule_url = url('admin/crontabs/checkSchedule');
        $js = <<<EOF
            <script type="text/javascript">
                 function checkSchedule() {
                    var schedule = $("#schedule").val();
                    $.post("{$checkSchedule_url}", {"schedule":schedule,_token:LA.token},
                    function(data){
                        if (data.status == false){
                            toastr.error(data.message);
                            return false;
                        }
                    }, "json");
                }
            
                $(function(){
                    checkSchedule();    // 页面加载后就执行一次
                    // 检查 Cron 表达式是否正确，如果正确，则获取预计执行时间
                    $("#schedule,#begin_at").blur(function(){
                        checkSchedule();
                    });
                });
            </script>
EOF;
        $form->html($js);

        $form->number('maximums', '最大执行次数')->default(0)->help("0为不限次数")->rules('required|integer|min:0',[
            'required'=>'最大执行次数不能为空',
            'integer'=>'最大执行次数必须为正整数',
            'min'=>'最大执行次数不能为负数',
        ]);
        $form->number('executes', '已执行次数')->default(0)->help("如果任务执行次数达到上限，则会自动把状态改为“完成”
如果已“完成”的任务需要再次运行，请重置本参数或者调整最大执行次数并把下面状态值改成“正常”");
        $form->datetime('begin_at', '开始时间')->default(date('Y-m-d H:i:s'))->help("如果设置了开始时间，则从开始时间计算；<br/>如果没有设置开始时间，则以当前时间计算。")->rules('required|date',['required'=>'开始时间不能为空','date'=>'时间格式不正确']);
        $form->datetime('end_at', '结束时间')->default(date('Y-m-d H:i:s'))->help("如果需要长期执行，请把结束时间设置得尽可能的久")->rules('required|date',['required'=>'结束时间不能为空','date'=>'时间格式不正确']);
        $form->number('weigh', '权重')->default(100)->help("多个任务同一时间执行时，按照权重从高到底执行")->rules('required|integer',['required'=>'权重不能为空','integer'=>'权重必须为正整数']);
        $form->select('status', '状态')->default('normal')->options(self::CRONTAB_STATUS)->rules('required|in:disable,normal,completed,expired',['required'=>'状态不能为空','in'=>'参数错误']);

        $form->tools(function (Form\Tools $tools) {
            // 去掉`查看`按钮
            $tools->disableView();
        });

        return $form;
    }

    /**
     * validate schedule.
     *
     * @return json
     */
    public function checkSchedule(Request $request){
        $schedule = $request->post('schedule','');
        if (empty($schedule)){
            return Response::json(['status'=>false,'message'=>'执行周期不能为空']);
        }
        if (!CronExpression::isValidExpression($schedule)){
            return Response::json(['status'=>false,'message'=>'执行周期 Cron 表达式错误']);
        }
        return Response::json(['status'=>true,'message'=>'']);
    }
}
