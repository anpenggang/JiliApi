<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/8/5
 * Time: 10:17
 */

namespace app\api\controller;


use think\Controller;
use think\facade\Request;
use app\api\validate\UserValidate;
use WechatApp\WeApp;
use app\common\model\User as UserModel;

class Login extends Controller
{

    // 获取短信验证码
    public function getPhoneCode(){
        if(Request::isPost()){
            $param = Request::param();
            $validate = new UserValidate();
            if(!$validate->sceneVerify()->check($param)){
                return json(['status'=>'fail' , 'error'=>$validate->getError()]);
            }
            // 发送验证码
            $code = rand(1000 , 9999);

//            $smsStatus = false;
//            $sms = new \SmsHelper\SmsApi253();
//            $smsResult = $sms->sendSMS($param['phone'] , "【图书借阅】您的动态验证码为{$code}，请在页面输入完成验证。如非本人操作请忽略。");
//            if(!is_null(json_decode($smsResult))){
//                $output=json_decode($smsResult,true);
//                if(isset($output['code'])  && $output['code']=='0'){
//                    $smsStatus = true;
//                }
//            }

            // 测试
            $smsStatus = true;
            $code = 8888;
            $smsResult = "OK";

            if(!$smsStatus){
                return json(['status'=>'fail' , 'error'=>'验证码发送失败']);
            }
            cache($param['phone'] , $code , 10*60);

            return json(['status'=>'ok' , 'message'=>'验证码获取成功' , 'data'=>$smsResult]);
        }
        return json(['status'=>'fail' , 'error'=> '未知的请求方式']);
    }


    // 授权登录
    public function userAuth(){
        if(Request::isPost()){
            $paramInput = Request::getInput();
            $param = Request::param();
            $code = Request::param('code');
            $iv = Request::param('iv');
            $validate = new UserValidate();
            if(!$validate->sceneAuth()->check($param)){
                return json(['status'=>'fail' , 'message'=>$validate->getError() , 'data'=>[$param , $paramInput , $code , $iv]]);
            }

            // 获取解密数据
            $wechatApp = new WeApp();
            list($status , $info) = $wechatApp->DeInfo($param['code'] , $param['iv'] , $param['encryptedData']);
            if(!$status){
                return json(['status'=>'fail' , 'data'=>$info , 'message'=>'用户信息获取失败']);
            }

            // 验证注册用户
            list($status , $user) = UserModel::authUser($info['data']);
            if(!$status){
                return json(['status'=>'fail' , 'message'=>$user , 'data'=>$info]);
            }

            // 设置user_token(cookie)
            $jsonArray = json_encode(['id'=>$user['id'] , 'time'=>time()]);
            $userToken = \Encode::encrypt($jsonArray);
            cookie('user_token' , $userToken);

            return json(['status'=>'ok' , 'message'=>"成功" , 'data'=>[
                'token' => $userToken
            ]]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 通过手机号码登录
    public function login(){
        if(Request::isPost()){
            $data['phone'] = Request::param('phone' , '');
            $data['phoneCode'] = Request::param('code' , '');

            $validate = new UserValidate();
            if(!$validate->scenePhoneLogin()->check($data)){
                return json(['status'=>'fail' , 'message'=>$validate->getError()]);
            }

            $onlineCode = cache($data['phone']);
            if($onlineCode != $data['phoneCode']){
                return json(['status'=>'fail' , 'message'=>'验证码错误']);
            }

            $user = UserModel::checkPhone($data['phone']);
            if(!$user){
                return json(['status'=>'fail' , 'message'=>'用户不存在，请使用微信一键登录']);
            }

            // 设置user_token(cookie)
            $jsonArray = json_encode(['id'=>$user['id'] , 'time'=>time()]);
            $userToken = \Encode::encrypt($jsonArray);
            cookie('user_token' , $userToken);

            return json(['status'=>'ok' , 'message'=>"成功" , 'data'=>[
                'token' => $userToken
            ]]);

        }
    }


}