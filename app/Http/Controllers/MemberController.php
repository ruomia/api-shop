<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

class MemberController extends Controller
{
    public function order(Request $req)
    {
        // 获取令牌中的数据
        echo $req->jwt-id;
    }
    public function store(Request $req)
    {
        // 生成验证器对象
        // 参数一、表单中的数据
        // 参数二、验证规则
        $validator = Validator::make($req->all(), [
            'username'=>'required|min:6|max:18|unique:users',
            'password'=>'required|min:6|max:18|confirmed',
            'mobile'=>[
                'required',
                'regex:/^1[34578][0-9]{9}$/'
            ],
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
        $member = User::create([
            'username' => $req->username,
            'password' => bcrypt($req->password),
            'mobile' => $req->mobile,
        ]);

        return ok($member);
    }

    public function login(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'username'=>'required|min:6|max:18',
            'password'=>'required|min:6|max:18',
        ]);
        if($validator->fails())
        {
            // 获取错误信息
            $errors = $validator->errors();
            // 返回 JSON 对象以及 422 的状态码
            return error($errors, 422);
        }
        // 根据用户名查询账号是否存在（只查询一条用 first 方法）
        $member = User::select('id','password')->where('username',$req->username)->first();
        if($member)
        {
            // 判断密码
            if(Hash::check($req->password, $member->password))
            {
                // 把用户的信息保存到令牌（JWT）中，然后把令牌发给前端
                $now = time();
                // 读取密钥
                $key = env('JWT_KEY');
                // 过期时间
                $expire = $now + env('JWT_EXPIRE');
                // 定义令牌中的数据
                $data = [
                    'iat' => $now,   //当前时间
                    'exp' => $expire,//过期时间
                    'id' => $member->id,
                ];
                // 生成令牌
                $jwt = JWT::encode($data, $key);

                // 发给前端
                return ok([
                    'ACCESS_TOKEN' => $jwt,
                ]);
            }
            else
            {
                return error('密码错误！',400);
            }
        }
        else
        {
            return error("用户名不存在！", 404);
        }
    }
}
