<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/4/12
 * Time: 10:45
 *
 * 用户登录管理
 */

namespace app\admin\controller;


use app\common\model\Admin;
use think\Controller;
use think\facade\Request;
use think\captcha\Captcha;

class Login extends Controller
{
    public function info(){
        phpinfo();
    }
    public function auth(){

        $auth = [
            '/form/advanced-form' => [
                'authority'=>['admin']
            ]
        ];

        return json($auth);
    }

    /**
     * 用户登录
     * @return \think\response\Json
     */
    public function account(){
        try{
            if(Request::isPost()) {
                $account = [];
                $param = Request::param();
                $account['param'] = $param;
                $account['status'] = 'fail';
                $account['currentAuthority'] = "";
                $account['type'] = Request::param('type');
                $userName = Request::param('userName');
                $password = Request::param('password');
                $captchaCode = Request::param('captcha');

                // 检查验证码是否正确
                $captcha = new Captcha();
                if(!$captcha->check($captchaCode , 1)){
                    return json(['status'=>'fail' , 'message'=>'验证码错误']);
                }

                // 登录数据检查
                $ret = Admin::checkAdminLogin($userName , $password);
                if($ret){
                    // 登录成功
                    setAccountSess($ret['id']);
                    $account['currentAuthority'] =  "user";     // 授权用户权限
                    $account['status'] = "ok";
                    return json($account);
                }
                $account['ret'] = $ret;
                return json($account);
            }
            return json(['status' => 'fail' , 'message' => '未知的请求方式']);
        }catch (\Exception $e){
            return json(['status' => 'fail' , 'message' => $e->getMessage()]);
        }
    }

    /**
     *  用户登出
     *  清空session
     * @return \think\response\Json
     */
    public function layout(){
        try{
            if(Request::isPost()){
                // 退出登录清空session
                setAccountSess(0 , true);
                return json(['status' => 'ok']);
            }
        }catch (\Exception $e){
            return json(['message' => 'exit failure' , 'status' => 'fail'] , 406);
        }
    }

    /**
     * 获取验证码
     */

    public function getCode(){
        $captcha  = new Captcha(["codeSet"=>"23456789"]);
        return $captcha->entry(1);
    }





}