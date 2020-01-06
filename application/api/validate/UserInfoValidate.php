<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/5/23
 * Time: 17:47
 */

namespace app\api\validate;


use think\Validate;

class UserInfoValidate extends Validate
{

    protected $rule =   [
        'phone'  => 'require|min:11|max:11',
        'code'   => 'require',
    ];

    protected $message = [
        'phone.require' => '电话号码必填',
        'phone.min'=> '电话号码必须为11位',
        'phone.max'=> '电话号码必须为11位',
        'code.require'=> '验证码不能为空',
    ];

    // 验证电话号码 and 验证码
    public function sceneCheckPhoneCode(){
        return $this->only(['phone','code']);
    }

}