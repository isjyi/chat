<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateSystemMessagesTable.
 */
class CreateSystemMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->nullable()->comment('接收用户id');
            $table->bigInteger('from_id')->unsigned()->nullable()->comment('来源相关用户id');
            $table->bigInteger('friend_group_id')->unsigned()->nullable()->comment('分组id');
            $table->string('remark')->nullable()->comment('添加好友附言');
            $table->tinyInteger('type')->unsigned()->default(0)->comment('0好友请求 1请求结果通知');
            $table->tinyInteger('status')->unsigned()->default(0)->comment('0未处理 1同意 2拒绝');
            $table->tinyInteger('read')->unsigned()->default(0)->comment('0未读 1已读，用来显示消息盒子数量');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('from_id')->references('id')->on('users');
            $table->foreign('friend_group_id')->references('id')->on('friend_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('system_messages');
    }
}
