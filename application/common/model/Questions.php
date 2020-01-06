<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 14:23
 */

namespace app\common\model;


use think\Model;

class Questions extends Model
{

    public static function queryData($where = [] , $start = 0 , $limit = 20 , $order = ['create_time'=>'desc']){
        $ret = self::where($where)
            ->limit($start , $limit)
            ->order($order)
            ->select()->toArray();

        return $ret;
    }

    public static function total($where = []){
        $total = self::where($where)->count();
        return $total;
    }


    // 随机抽取试题
    public static function getQuestion( $number = 5 ){
        $where =[];
        $dataIds = self::where($where)->field('id')->select()->toArray();
        if(empty($dataIds)){
            return [];
        }
        $ids = array_column($dataIds , 'id');
        if(count($ids)>$number) {
            $sortIds = shuffle($ids);       // 打乱数组
            $idNum = array_slice($sortIds , $number-1);     // 获取指定个数
        }else{
            $idNum = $ids;
        }

        $data = self::where('id','in',$idNum)->select()->toArray();

        foreach ($data as &$value){
            self::checkInfo($value);
        }

        return $data;
    }

    // 判断考试分数
    public static function checkNumber($answer = []){

        $success = 0;
        foreach ($answer as $key=>$value){
            $data = self::where('id' , $value['id'])->findOrEmpty()->toArray();

            if($data['option']==$value['val']){
                $success++;
            }
        }

        return [$success/count($answer) , ['success'=>$success , 'total'=>count($answer)]];
    }

    private static function checkInfo(&$data){
//        if(isset($data['option'])) unset($data['option']);
    }

}