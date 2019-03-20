Crontab extension for laravel-admin
======

[Crontab](https://github.com/ArrowJustDoIt/Crontab)是一个laravel-admin后台的定时任务扩展插件,你可以通过插件定时执行shell、sql以及访问指定连接

## 截图
![列表](https://raw.githubusercontent.com/ArrowJustDoIt/crontab/master/extension_index.png)

![创建页面](https://raw.githubusercontent.com/ArrowJustDoIt/crontab/master/extension_create.png)

## 安装

```bash
composer require ArrowJustDoIt/crontab
```

然后
```bash
php artisan vendor:publish --provider=Encore\Crontab\CrontabServiceProvider
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
``````
License
------------
Licensed under [The MIT License (MIT)](LICENSE).