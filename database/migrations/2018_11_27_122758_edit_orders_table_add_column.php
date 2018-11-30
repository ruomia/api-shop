<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditOrdersTableAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('name',50)->comment('收件人姓名');
            $table->string('tel',11)->comment('手机号');
            $table->string("province",20)->comment('省份');
            $table->string('city',20)->comment('城市');
            $table->string('area',20)->comment('区级');
            $table->string('address')->comment('详细地址');
            $table->tinyInteger('status')->comment('订单状态，等待买家付款1、买家已付款2、未发货3、部分发货4、已发货5、物流派件中6、快件已签收7、交易成功8');
            $table->dropColumn(['address_id','method','state']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['name','tel','province','city','area','address','status']);
            
        });
    }
}
