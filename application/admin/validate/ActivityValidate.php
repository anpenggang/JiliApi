<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/31
 * Time: 11:36
 */

namespace app\admin\validate;


use think\Validate;

class ActivityValidate extends Validate
{
    protected $rule =   [
        'title' => 'require',
        'content' => 'require',
        'start_time' => 'require',
        'end_time' => 'require',
        'store' => 'require',
        'image' => 'require',
    ];

    protected $message  =   [
        'title.require' => '主题名称不能为空',
        'content.require' => '请输入活动规则',
        'start_time.require' => '参与时间不能为空',
        'end_time.require' => '参与时间不能为空.',
        'store.require' => '请输入门店',
        'image.require' => '请选择活动图片',
    ];

    public function sceneCreateEditActivity(){
        return $this->only(['title','content','start_time','end_time','store','image']);
    }

}