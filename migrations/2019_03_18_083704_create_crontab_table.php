<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrontabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crontab', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',10)->comment('类型');
            $table->string('title',150)->comment('标题');
            $table->text('contents')->comment('内容');
            $table->string('schedule',100)->comment('Cron表达式');
            $table->tinyInteger('sleep')->default(0)->comment('延迟秒数执行');
            $table->integer('maximums')->default(0)->comment('最大执行次数 0为不限');
            $table->integer('executes')->default(0)->nullable()->comment('已经执行的次数');
            $table->dateTime('begin_at')->comment('开始时间');
            $table->dateTime('end_at')->comment('结束时间');
            $table->dateTime('execute_at')->nullable()->comment('最后执行时间');
            $table->integer('weigh')->default(0)->comment('权重');
            $table->enum('status',['completed','expired','disable','normal'])->default('normal')->comment('状态');
            $table->timestamps();
        });
        Schema::create('crontab_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',10)->comment('类型');
            $table->integer('cid')->comment('任务的ID');
            $table->string('title',150)->comment('标题');
            $table->mediumText('remark')->comment('备注');
            $table->tinyInteger('status')->comment('状态 0:失败 1:成功');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crontab');
        Schema::dropIfExists('crontab_log');
    }
}
