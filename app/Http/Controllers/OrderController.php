<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderSku;
use App\Models\Address;
use App\Models\Skus;
use DB;
use Validator;
class OrderController extends Controller
{
    public function index(Request $req)
    {
        $perpage = max(1, (int)$req->perpage);
        
        $data = Order::with('skus')
        ->where('user_id',$req->jwt->id)
        ->orderBy('id','desc')
        ->paginate($perpage);

        return ok($data);
    }
    public function store(Request $req)
    {
        // 表单验证
        $validator = Validator::make($req->all(), [
            'address_id'=>'required',
            'goods'=>'required|json',
        ]);
        if($validator->fails())
        {
            // 获取错误信息
            $errors = $validator->errors();
            // 返回 JSON对象以及422 的状态码
            return error($errors, 422);
        }

        // 根据收件人ID查询出收货人信息
        $address = Address::find($req->address_id);
        if(!$address)
        {
            // 返回 JSON 对象以及  422 的状态码
            return error([
                'address'=>'无效的收件人地址'
            ], 422);
        }
        // 验证购物车中商品的库存量并计算总价
        $goodsInfo = json_decode($req->goods, TRUE);
        // 循环要购买的每件商品，检查库存量，同时在计算出总价
        $totalFee = 0;
        foreach($goodsInfo as $k => $v)
        {
            $skuInfo = Skus::select('stock','price','goods_id')->find($v['sku_id']);
            if($skuInfo->stock > $v['count'])
            {
                $totalFee += $skuInfo->price * $v['count'];
                // 把这件商品的price 和 goods_id 放到购物车这个数组中，后面下订单要使用
                $goodsInfo[$k]['price'] = $skuInfo->price;
                $goodsInfo[$k]['goods_id'] = $skuInfo->goods_id;
            }
            else
            {
                return error('库存量不足！', 403);
            }
        }
        /* 生成订单号并构造下订单的信息  */
        $sn = getOrderSn();
        $data = [
            'number' => $sn,
            'user_id'=>$req->jwt->id,
            'real_payment'=>$totalFee,
            'name'=>$address->name,
            'tel'=>$address->tel,
            'province'=>$address->province,
            'city'=>$address->city,
            'area'=>$address->area,
            'address'=>$address->address,
            'status'=> 0,
        ];
        /* 开启事务 */
        DB::beginTransaction();
        /* 把订单的基本信息保存到 订单表 */
        $order = Order::create($data);
        if($order)
        {
            /* 把购物车中的商品保存到 订单商品表中 */
            $_cartData = [];
            // 循环购物车中的商品
            foreach($goodsInfo as $v)
            {
                $_cartData[] = new OrderSku([
                    'sku_id' => $v['sku_id'],
                    'goods_id' => $v['goods_id'],
                    'price' => $v['price'],
                    'count' => $v['count'],
                ]);
                /* 减少相应商品的库存量 */
                if(!Skus::where('id',$v['sku_id'])->decrement('stock',$v['count']))
                {
                    DB::rollback();
                    return error('下单失败！', 500);
                }
            }
            // 循环购物车中的商品，插入到订单商品表中
            // $order->skus() : 取出订单模型所关联的模型
            // 向关联模型中一次插入多条记录

            if($order->skus()->saveMany($_cartData))
            {
                DB::commit();
                return ok($order);
            }
            else
            {
                DB::rollback();
                return error('下单失败！', 500);
            }
        }
        else
        {
            DB::rollback();
            return error('下单失败，请重试', 500);
        }
    }
}
