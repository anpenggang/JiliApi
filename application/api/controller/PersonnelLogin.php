<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/23
 * Time: 13:55
 */

namespace app\api\controller;


use app\common\model\Admin;
use think\Controller;
use think\facade\Request;

class PersonnelLogin extends Controller
{

    // 前段工作人员登录
    public function login(){
        if(Request::isPost()){
            $account = Request::param('account' , '');
            $password = Request::param('password' , '');

            $user = Admin::checkWorkLogin($account , $password);
            if(!$user){
                return json(['status'=>'fail' , 'message'=>'密码和账号错误' , 'data'=>$user]);
            }

            // 设置personnel_token(cookie)
            $jsonArray = json_encode(['id'=>$user['id'] , 'time'=>time()]);
            $token = \Encode::encrypt($jsonArray);
            cookie('personnel_token' , $token);

            return json(['status'=>'ok' , 'message'=>"成功" , 'data'=>[
                'token' => $token
            ]]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

}