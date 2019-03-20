<?php

namespace ArrowJustDoIt\Crontab;

use Encore\Admin\Extension;

class Crontab extends Extension
{
    public $name = 'crontab';

    public $migrations = __DIR__.'/../migrations/';

    public $menu = [
        'title' => '定时任务',
        'path'  => 'crontab',
        'icon'  => 'fa-gears',
    ];
}