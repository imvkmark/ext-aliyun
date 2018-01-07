<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration auto-generated by Sequel Pro Laravel Export
 * @see https://github.com/cviebrock/sequel-pro-laravel-export
 */
class CreateSysActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_activity', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cat_id');
            $table->unsignedInteger('game_id');
            $table->string('title', 100)->comment('活动标题');
            $table->string('image', 255)->comment('活动图片');
            $table->string('link_url', 255)->comment('活动规则');
            $table->dateTime('started_at')->nullable()->comment('活动开始时间');
            $table->dateTime('ended_at')->nullable()->comment('结束时间');
            $table->unsignedTinyInteger('is_open')->comment('是否开启\n0:否1:是');
            $table->dateTime('created_at')->nullable()->comment('创建时间');
            $table->dateTime('updated_at')->nullable()->comment('修改时间');

            

            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_activity');
    }
}
