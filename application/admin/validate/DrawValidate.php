<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/31
 * Time: 11:36
 */

namespace app\admin\validate;


use think\Validate;

class DrawValidate extends Validate
{
    protected $rule =   [
        'name' => 'require',
        'chance'  => 'require',
        'effective_start_time' => 'require',
        'effective_end_time' => 'require',
        'count' => 'require',
        'over_count' => 'require',
        'store' => 'require',
        'remark' => 'require',
    ];

    protected $message  =   [
        'name.require' => '名称不能为空',
        'chance.require' => '请输入活动规则',
        'effective_start_time.require' => '参与时间不能为空',
        'effective_end_time.require' => '参与时间不能为空.',
        'count.require' => '请输入门店',
        'over_count.require' => '请选择活动图片',
        'store.require' => '可使用店铺不能为空',
        'remark.require' => '备注不能为空',
    ];

    public function sceneCreateEditDraw(){
        return $this->only(['name','effective_start_time','effective_end_time','remark']);
    }


}