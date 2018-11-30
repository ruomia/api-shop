<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Skus;
use DB;
class GoodsController extends Controller
{
    public function index(Request $req)
    {
        if($req->id)
        {
            // 获取单个商品
            $id = max(1, (int)$req->id);
            $data = Goods::with('attribute','sku')
                        ->where('id',$id)
                        ->first();
            if($data)
                return ok($data);
            else    
                return error('商品不存在',404);
        }
        else if($req->sku_id)
        {
            // 查询购物车中的商品
            $data = Skus::with('goods')
                        ->whereIn('id',explode(',', $req->sku_id))
                        ->get();
            if($data)
                return ok($data);
            else    
                return error('商品不存在',404);
        }
        else
        {
            // 获取部分商品
            $perPage = max(1, (int)$req->per_page);
            $order = ($req->order=='asc' || $req->order=='desc') ? $req->order : 'desc' ;
            $sortby = ($req->sortby=='id' || $req->sortby=='created_at') ? $req->sortby : 'id' ;
            $data = Goods::with('attribute','sku')
                            ->where('name','like', '%'.$req->keywordse.'%')
                            ->orderBy($sortby, $order)
                            ->paginate($perPage);
            return ok($data);
        }
        
    }
    public function attribute(Request $req)
    {
        $data = Skus::where('goods_id',$req->goods_id)->get();
        $str = [];
        foreach($data as $v)
        {
            // $str .= $v->attribute;
            $attr =  explode(',', $v->attribute);
            foreach($attr as $s)
            {
                $str[] = $s;
            }

        }
        
        // $arr = array_merge($str);
        // $arr = array_merge($str);
        // $arr = array_slice($str,4);
        $str = array_unique($str);
        $data = DB::table('attribute_values as a')
        ->select(DB::raw('a.id'),'name','value')
        ->leftJoin('attribute_names as b','a.name_id','=','b.id')
        ->whereIn('a.id',$str)
        ->orderBy('b.id','asc')
        ->get();

        $attrs = [];
        foreach($data as $k=>$v)
        {
            // $attrs[$v->name][$v->id] = $v->value;
            // $attrs[$v->name][]['value'] = $v->value;
            // $attrs[$v->name][]['id'] = $v->id;
            // $attrs[$v->name][]['checked'] = false;
            $attrs[$v->name][] = [
                'id'=>$v->id,
                'value'=>$v->value,
                'checked'=>false,
            ];
        }
        return ok($attrs);
        // return $str1;
    }
    public function getSku(Request $req)
    {
        $data = Skus::where('attribute',$req->attr_id)->first();
        return ok($data);
    }
}
