<?php

namespace Encore\Crontab\Http\Models;

use Illuminate\Database\Eloquent\Model;

class CrontabLog extends Model
{
    //
    protected $table = 'crontab_log';
    protected $fillable = ['type'];

    public function crontab(){
        return $this->belongsTo(Crontab::class, 'cid', 'id');
    }

    public function getCidLinkAttr($value, $data)
    {
        $url = url('crontab/index/index', ['search_field'=>'id', 'keyword'=>$data['cid']]);
        return '<a href="'.$url.'">'.$data['cid'].'</a>';
    }

    public function getTitleLinkAttr($value, $data)
    {
        $url = url('crontab/index/index', ['search_field'=>'id', 'keyword'=>$data['cid']]);
        return '<a href="'.$url.'">'.$data['title'].'</a>';
    }

    public function getStatusTextAttr($value, $data)
    {
        if ($data['status']==1){
            return '成功';
        }else{
            return '失败';
        }
    }

    public function getExecuteTimeAttr($value, $data)
    {
        if (empty($data['create_time'])){
            return null;
        }
        return date('Y-m-d H:i:s', $data['create_time']);
    }
}
