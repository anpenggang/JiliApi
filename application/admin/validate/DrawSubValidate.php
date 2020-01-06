<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/31
 * Time: 11:36
 */

namespace app\admin\validate;


use think\Validate;

class DrawSubValidate extends Validate
{
    protected $rule =   [
        'name' => 'require',
        'address' => 'require',
        'chance'  => 'require',
        'count' => 'require'
    ];

    protected $message  =   [
        'name.require' => '店铺名称不能为空',
        'address.require' => '店铺地址不能为空',
        'chance.require' => '请输入中奖概率',
        'count.require' => '请输入数量',
    ];

    public function sceneCreateEditDraw(){
        return $this->only(['name','address','chance','count']);
    }

}