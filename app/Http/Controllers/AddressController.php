<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Validator;
class AddressController extends Controller
{
    public function index(Request $req)
    {
        $data = Address::where('user_id',$req->jwt->id)->get();
        return ok($data);
    }
    public function store(Request $req)
    {
        // 生成验证器对象
        // 参数一、表单中的数据
        // 参数二、验证规则
        $validator = Validator::make($req->all(), [
            'name'=> 'required',
            'tel' => 'required|regex:/^1[34578][0-9]{9}$/',
            'province'=>'required',
            'city'=>'required',
            'area'=>'required',
            'address' => 'required',
            'default'=>'required|min:0|max:1',
        ]);
        // 如果失败
        if($validator->fails())
        {
            // 获取错误信息
            $errors = $validator->errors();
            // 返回 JSON 对象以及 422 的状态码
            return error($errors, 422);
        }
        // 插入数据库
        // 返回值：插入成功之后那条记录的对象
        // $data = $req->all();
        $model = new Address;
        $model = $model->fill($req->all());
        $model->user_id = $req->jwt->id;
        // 保存
        $model->save();

        return ok($model);

    }
}
