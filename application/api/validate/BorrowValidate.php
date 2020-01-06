<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/31
 * Time: 17:53
 */

namespace app\api\validate;


use think\Validate;

class BorrowValidate extends Validate
{
    protected $rule =   [
        'book_id' => 'require',
        'amount_id' => 'require',
    ];

    protected $message  =   [
        'book_id.require' => '请选择借阅书籍',
        'amount_id.require' => '请选择借阅天数',
    ];

    public function sceneBorrowBook(){
        return $this->only(['book_id','amount_id']);
    }

}