<?php

namespace ArrowJustDoIt\Crontab\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Crontab extends Model
{
    //
    protected $table = 'crontab';

    public function crontabLog(){
        return $this->hasMany(CrontabLog::class, 'cid', 'id');
    }
}
