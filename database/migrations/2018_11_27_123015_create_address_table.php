<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('user_id')->comment('用户ID');
            $table->string('name',50)->comment('收件人姓名');
            $table->string('tel',11)->comment('手机号');
            $table->string("province",20)->comment('省份');
            $table->string('city',20)->comment('城市');
            $table->string('area',20)->comment('区级');
            $table->string('address')->comment('详细地址');
            $table->tinyInteger('default')->default(0)->comment('默认使用地址：1');
            $table->engine = "InnoDB";
            $table->comment = "用户地址表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address');
    }
}
