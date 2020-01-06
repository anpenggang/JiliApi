<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/5/23
 * Time: 17:47
 */

namespace app\admin\validate;


use think\Validate;

class AdminValidate extends Validate
{

    protected $rule =   [
        'hisPassword' => 'require',
        'newPassword' => 'require',
        'newPasswordConf' => 'require',
        'account' => 'require',
        'type' => 'require'
    ];

    protected $message  =   [
        'hisPassword.require' => '请输入旧密码',
        'newPassword.require' => '请输入新密码',
        'newPasswordConf.require' => '请输入确认新密码',
        'account.require' => '账号不能为空',
        'type.require' => '请选择类型'
    ];

    // 修改密码
    public function sceneUpdatePass(){
        return $this->only(['newPasswordConf','hisPassword','newPassword']);
    }

    // 创建账号
    public  function sceneCreateAccount(){
        return $this->only(['account','type']);
    }
}