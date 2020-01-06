<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/8/7
 * Time: 17:26
 */

namespace app\api\validate;


use think\Validate;

class BookValidate extends Validate
{

    protected $rule =   [
        'name' => 'require|max:16',
        'introduce' => 'require|max:150',
        'class_id' => 'require|number',
        'lead_image' => 'require',
        'images' => 'require',
        'author' => 'require',
        'press' => 'require',
        'press_date' => 'require',
        'borrow_amount' => 'require',
    ];

    protected $message  =   [
        'name.require' => '请输入书籍名称',
        'name.max' => '书籍名称不能超过16个字符',
        'introduce.require' => '请输入简介',
        'introduce.max' => '书籍简介不能超过150个字符',
        'class_id.require' => '请选择小区',
        'class_id.number' => '小区选择异常',
        'lead_image.require' => '请选择封面图片',
        'images.require' => '请输入书籍介绍',
        'author.require' => '请输入作者',
        'press.require' => '请输入出版社',
        'press_date.require' => '请输入出版时间',
        'borrow_amount.require' => '请输入借阅积分',
    ];

    public function sceneCreateBook(){
        return $this->only(['name','introduce','class_id','amount','lead_image','images','author','press','press_date','borrow_amount']);
    }


}