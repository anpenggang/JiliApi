<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/31
 * Time: 11:36
 */

namespace app\admin\validate;


use think\Validate;

class QuestionValidate extends Validate
{
    protected $rule =   [
        'subject' => 'require',
        'option_a' => 'require',
        'option_b' => 'require',
        'option_c' => 'require',
        'option' => 'require',
    ];

    protected $message  =   [
        'subject.require' => '主题名称不能为空',
        'option_a.require' => '请输入A选项',
        'option_b.require' => '请输入B选项',
        'option_c.require' => '请输入C选项',
        'option.require' => '请选择正确答案',
    ];

    public function sceneCreateEditQuestion(){
        return $this->only(['subject','option_a', 'option_b', 'option_c', 'option']);
    }

}