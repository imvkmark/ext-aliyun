<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration auto-generated by Sequel Pro Laravel Export
 * @see https://github.com/cviebrock/sequel-pro-laravel-export
 */
class CreateSysAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_area', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 45)->comment('编码');
            $table->string('province', 20)->comment('省份');
            $table->string('city', 20)->comment('城市');
            $table->string('district', 20)->comment('区');
            $table->string('parent', 45)->comment('父级');

            

            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_area');
    }
}
