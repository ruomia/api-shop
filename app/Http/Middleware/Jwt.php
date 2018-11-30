<?php

namespace App\Http\Middleware;

use Closure;
use \Firebase\JWT\JWT as JWTCHECK;

class Jwt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        /* 说明：客户端在提交令牌时，是把令牌放到 http 协议头中（不是表单中！！）
                并且 JWT 规定前7个字符必须是 bearer （后面这里有个空格）
            HTTP_AUTHORIZATION: bearer fdkl;ajsf;dsajlfjl;jxxxxx
            所以我们在获取令牌时，要从 $_SERVER 中获取，不是 $_POST
            在 Laravel 中要获取 $_SERVER 使用  $request->server 函数
        */
        // 从协议头是取出令牌
        $jwt = substr($request->server('HTTP_AUTHORIZATION'), 7);
        try
        {
            // 解析 token
            $jwt = JWTCHECK::decode($jwt, env('JWT_KEY'), array('HS256'));
            // 把解析出来的数据保存到 Request 对象中的 jwt 属性上，将来在控制器中就可能 $req->jwt 这样来获取了
            $request->jwt = $jwt;

            // 继续执行下一个中间件
            return $next($request);
        }
        catch(\Exception $e)
        {
            // 返回错误信息
            return response([
                'status_code'=>'403',
                'message'=>'HTTP/1.1 403 Forbidden'
            ]);
        }
    }
}