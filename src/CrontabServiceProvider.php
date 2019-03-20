<?php

namespace ArrowJustDoIt\Crontab;

use Illuminate\Support\ServiceProvider;

class CrontabServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function boot(Crontab $extension)
    {
        if (! Crontab::boot()) {
            return ;
        }

        //数据迁移
        if ($migrations = $extension->migrations()) {
            $this->loadMigrationsFrom($migrations);
        }

        //命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                autoTask::class,
            ]);
        }

        $this->app->booted(function () {
            //路由
            Crontab::routes(__DIR__.'/../routes/web.php');
        });
    }
}