<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/22
 * Time: 13:47
 *
 * 答题记录
 */

namespace app\common\model;


use think\Model;

class AnswerRecord extends Model
{

    // 获取记录内容
    public static function queryOne($id=0){
        return self::where('id' , $id)->findOrEmpty()->toArray();
    }

    // 检查是否可抽奖
    public static function checkAnswer($id=0){
        $info = self::queryOne($id);
        if(empty($info)){
            return [false , '答题记录不存在' , $info];
        }
        if(!(isset($info['subject_number']) && $info['subject_number']>0.6)){
            return [false , '答题未通过' , $info];
        }
        if(!(isset($info['status']) && $info['status']==0)){
            return [false , '今日你已抽奖哦！' , $info];
        }

        return [true , '' , $info];
    }

}