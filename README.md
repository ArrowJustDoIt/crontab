Crontab extension for laravel-admin
======

[Crontab](https://github.com/ArrowJustDoIt/Crontab)是一个laravel-admin后台的定时任务扩展插件,你可以通过此插件定时执行shell、sql以及访问指定链接

## 截图
![crontab列表](https://raw.githubusercontent.com/ArrowJustDoIt/crontab/master/crontab_list.png)

![crontab创建](https://raw.githubusercontent.com/ArrowJustDoIt/crontab/master/crontab_create.png)

![crontablog列表](https://raw.githubusercontent.com/ArrowJustDoIt/crontab/master/crontab_log_list.png)

![crontablog详情](https://raw.githubusercontent.com/ArrowJustDoIt/crontab/master/crontab_log_detail.png)
## 安装

```bash
composer require arrowjustdoit/crontab
php artisan migrate
```

## 配置

在`config/admin.php`文件的`extensions`配置部分，加上属于这个扩展的配置
```php

    'extensions' => [

        'crontab' => [
        
            // 如果要关掉这个扩展，设置为false
            'enable' => true,
        ]
    ]

```

在服务器中配置crontab

```
crontab -e #回车
#>>后面为日志文件,可加可不加
* * * * * php /your web dir/artisan autotask:run >>/home/crontab.log 2>&1 
```

## 访问

```
https://your domain/admin/crontabs #定时任务列表
https://your domain/admin/crontabLogs #定时任务日志列表
```


## License

Licensed under [The MIT License (MIT)](LICENSE).