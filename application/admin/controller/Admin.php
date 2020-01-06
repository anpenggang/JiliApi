<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/4
 * Time: 15:43
 */

namespace app\admin\controller;


use app\admin\validate\AdminValidate;
use app\common\controller\AdminBase;
use app\common\model\Admin as AdminModel;
use think\captcha\Captcha;
use think\facade\Request;

class Admin extends AdminBase
{
    // 获取管理员信息
    public function userInfo(){
        $info = AdminModel::checkUser($this->admin_id);

        if(!empty($info)){
            return json($info);
        }

        return json(['status' =>'fail' , 'error'=>'失败' , 'data'=>$info]);
    }

    // 重置密码
    public function resetPassword(){
        if(Request::isPost()){
            $param = Request::param();
            $code = Request::param('code');
            $oldPassword = Request::param('hisPassword');
            $newPassword = Request::param('newPassword');
            $newPasswordConf = Request::param('newPasswordConf');
            $validate = new AdminValidate();
            if(!$validate->sceneUpdatePass()->check($param)){
                return json(['status'=>'fail' , 'message' => $validate->getError()]);
            }
            // 检查验证码
            $captcha = new Captcha();
            if(!$captcha->check($code , 1)){
                return json(['status'=>'fail' , 'message'=>'验证码错误']);
            }
            // 检查密码是否正确
            $password = $this->user['password'];
            if($password !== encryptPass($oldPassword)){
                return json(['status' => 'fail' , 'message'=>'旧密码错误' ]);
            }
            // 比较新密码是否相同
            if(encryptPass($newPassword) !== encryptPass($newPasswordConf)){
                return json(['status' => 'fail' , 'message'=>'新密码不相同' ]);
            }
            // 比较新旧密码是否相同
            if($password === $newPassword){
                return json(['status' => 'fail' , 'message'=>'新旧密码相同']);
            }
            // 设置新密码
            $user = AdminModel::update(['password'=>encryptPass($newPassword) , 'id'=>$this->admin_id]);

            if($user){
                return json(['status'=>'ok' , 'message'=>'密码修改成功' ]);
            }

            return json(['status'=>'fail' , 'message'=>'密码修改失败' ]);
        }
        return json(['status'=>'fail' , 'message'=>'未知的请求方式']);
    }
}