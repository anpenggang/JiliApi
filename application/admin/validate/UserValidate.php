<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/31
 * Time: 11:36
 */

namespace app\admin\validate;


use think\Validate;

class UserValidate extends Validate
{
    protected $rule =   [
        'id' => 'require',
        'type' => 'require',
    ];

    protected $message  =   [
        'id.require' => '请选择用户',
        'type.require' => '请确认操作类型',
    ];

    public function sceneCheckUserStatus(){
        return $this->only(['id','type']);
    }

}