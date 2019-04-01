<?php

namespace ArrowJustDoIt\Crontab;

use Illuminate\Console\Command;
use ArrowJustDoIt\Crontab\Http\Models\Crontab;
use ArrowJustDoIt\Crontab\Http\Models\CrontabLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Cron\CronExpression;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;


class autoTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autotask:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时任务总调度';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 筛选未过期且未完成的任务
        $crontab_list = Crontab::where(['status' => 'normal'])->orderBy('weigh','desc')->orderBy('id','desc')->get();
        if (!$crontab_list) {
            return null;
        }
        $time = time();
        foreach ($crontab_list as $key => $crontab) {
            $value = $crontab->toArray();
            $execute = false;   // 是否执行

            if ($time < strtotime($value['begin_at'])) {   //任务未开始
                continue;
            }

            if ($value['maximums'] && $value['executes'] > $value['maximums']) {  //任务已超过最大执行次数
                $crontab->status = 'completed';
            } else if (strtotime($value['end_at']) > 0 && $time > strtotime($value['end_at'])) {     //任务已过期
                $crontab->status = 'expired';
            } else {
                $cron = CronExpression::factory($value['schedule']);
                /*
                 * 根据当前时间判断是否该应该执行
                 * 这个判断和秒数无关，其最小单位为分
                 * 也就是说，如果处于该执行的这个分钟内如果多次调用都会判定为真
                 * 所以我们在服务器上设置的定时任务最小单位应该是分
                 */
                if ($cron->isDue()) {
                    // 允许执行
                    $execute = true;
                    // 允许执行的时候更新状态
                    $crontab->execute_at = date('Y-m-d H:i:s');
                    $crontab->executes = $value['executes'] + 1;
                    $crontab->status = ($value['maximums'] > 0 && $crontab->executes >= $value['maximums']) ? 'completed' : 'normal';
                } else {    //如果未到执行时间则跳过本任务去判断下一个任务
                    continue;
                }
            }
            // 更新状态
            $crontab->save();
            // 如果不允许执行，只是从当前开始已过期或者已超过最大执行次数的任务，只是更新状态就行了，不执行
            if (!$execute) {
                continue;
            }

            try {
                // 分类执行任务
                switch ($value['type']) {
                    case 'url':
                        try {
                            $client = new Client();
                            $response = $client->request('GET', $value['contents']);
                            $this->saveLog('url', $value['id'], $value['title'], 1, $value['contents'] . ' 请求成功，HTTP状态码: ' . $response->getStatusCode());
                        } catch (RequestException $e) {
                            $this->saveLog('url', $value['id'], $value['title'], 0, $value['contents'] . ' 请求成功失败: ' . $e->getMessage());
                        }
                        break;
                    case 'sql':
                        /* 注释中的方法可以一次执行所有SQL语句
                        // 执行SQL
                        $count  = DB::select($crontab['contents']);
                        dump($count );*/

                        // 解析成一条条的sql语句
                        $sqls = str_replace("\r", "\n", $value['contents']);
                        $sqls = explode(";\n", $sqls);
                        $remark = '';
                        $status = 1;
                        foreach ($sqls as $sql) {
                            $sql = trim($sql);
                            if (empty($sql)) continue;
                            if (substr($sql, 0, 2) == '--') continue;   // SQL注释
                            // 执行SQL并记录执行结果
                            if (false !== DB::select($sql)) {
                                $remark .= '执行成功: ' . $sql . "\r\n\r\n";
                            } else {
                                $remark .= '执行失败: ' . $sql . "\r\n\r\n";
                                $status = 0;
                            }
                        }
                        $this->saveLog('sql', $value['id'], $value['title'], $status, $remark);
                        break;
                    case 'shell':
                        $status = 0;
                        $request = 'fail';

                        $process = new Process($value['contents']);
                        $process->run();
                        if ($process->isSuccessful()) {
                            $status = 1;
                            $request = $process->getOutput();
                        }
                        $this->saveLog('shell', $value['id'], $value['title'], $status, $request);
                        break;
                }
            }
            catch (Exception $e)
            {
                $this->saveLog($value['type'], $value['id'], $value['title'], 0, "执行的内容发生异常:\r\n" . $e->getMessage());
            }

            print_r('执行完毕');
        }
    }

    // 保存运行日志
    private function saveLog($type, $cid, $title, $status, $remark = '')
    {
        $crontLog = new CrontabLog();
        $crontLog->type = $type;
        $crontLog->cid = $cid;
        $crontLog->title = $title;
        $crontLog->status = $status;
        $crontLog->remark = $remark;
        $crontLog->save();
    }
}
