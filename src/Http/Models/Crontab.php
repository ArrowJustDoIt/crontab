<?php

namespace Encore\Crontab\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;

class Crontab extends Model
{
    //
    protected $table = 'crontab';

    public function crontabLog(){
        return $this->hasMany(CrontabLog::class, 'cid', 'id');
    }
    // 获取下次执行时间
    public function getNextTimeAttr($value, $data){
        $cron = CronExpression::factory($data['schedule']);
        return $cron->getNextRunDate()->format('Y-m-d H:i');
    }

    public function getMaximumsTextAttr($value, $data){
        if ($data['maximums']>0){
            return $data['maximums'];
        }else{
            return '无限制';
        }
    }

    public function setBeginTimeAttr($value)
    {
        return strtotime($value);
    }

    public function setEndTimeAttr($value)
    {
        return strtotime($value);
    }

    public function getBeginTimeAttr($value)
    {
        if (empty($value)){
            return null;
        }
        return date('Y-m-d H:i', $value);
    }

    public function getEndTimeAttr($value)
    {
        if (empty($value)){
            return null;
        }
        return date('Y-m-d H:i', $value);
    }

    public function getExecuteTimeAttr($value)
    {
        if (empty($value)){
            return null;
        }
        return date('Y-m-d H:i', $value);
    }
}
