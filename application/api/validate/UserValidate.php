<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/5/23
 * Time: 17:47
 */

namespace app\api\validate;


use think\Validate;

class UserValidate extends Validate
{

    protected $rule =   [
        'phone'  => 'require|min:11|max:11',
        'code'   => 'require',
        'phoneCode'   => 'require',
        'iv'     => 'require',
        'encryptedData'     => 'require',
    ];

    protected $message  =   [
        'phone.require' => '电话号码必填',
        'phone.min'=> '电话号码必须为11位',
        'phone.max'=> '电话号码必须为11位',
        'code.require'=> '小程序登录code不能为空',
        'iv.require'=> 'iv不能为空',
        'encryptedData.require'=> '用户信息不能为空',
        'phoneCode.require'=> '验证码不能为空',
    ];

    // 获取验证码
    public function sceneVerify(){
        return $this->only(['phone']);
    }

    // 授权信息
    public function sceneAuth(){
        return $this->only(['code','iv','encryptedData']);
    }

    // 电话号码登录
    public function scenePhoneLogin(){

        return $this->only(['phone','phoneCode']);
    }

}