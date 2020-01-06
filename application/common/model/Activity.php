<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 11:24
 * 活动数据
 */

namespace app\common\model;


use think\Model;

class Activity extends Model
{

    public static function queryData($where = [] , $start = 0 , $limit = 20 , $order = ['create_time'=>'desc']){
        $ret = self::where($where)
            ->limit($start , $limit)
            ->order($order)
            ->select()->toArray();

        self::checkDataInfo($ret);

        return $ret;
    }

    public static function total($where = []){
        $total = self::where($where)->count();
        return $total;
    }

    // 检查活动是否有效
    public static function checkActivity($id=0){
        $where = [
            ['status','=',1],
            ['start_time' , '<=' , date("Y-m-d H:i:s")],
            ['end_time' , '>=' , date("Y-m-d H:i:s")],
            ['id' , '=' , $id]
        ];

        $data = self::where($where)->findOrEmpty()->toArray();
        return $data?:false;
    }

    // 获取有效活动
    public static function getOneActivity(){
        $where = [
            ['status','=',1],
            ['start_time' , '<=' , date("Y-m-d H:i:s")],
            ['end_time' , '>=' , date("Y-m-d H:i:s")],
        ];
        $order = [
            'create_time'=>'desc'
        ];
        $data = self::where($where)->order($order)->findOrEmpty()->toArray();

        self::checkInfo($data);

        return $data;
    }

    // 获取活动
    public static function queryOne($id = 0){
        $info = self::where('id' , $id)->findOrEmpty()->toArray();
        self::checkInfo($info);
        return $info;
    }

    // 检查是否存在活动中的数据
    public static function checkOneActivity(){
        $where = [
            ['status','=',1]
        ];
        $data = self::where($where)->findOrEmpty()->toArray();
        return $data;
    }

    private static function checkInfo(&$info){
//        if(!empty($info['store'])) $info['store'] = json_decode($info['store'] , true);
        if(!empty($info['image'])){
            $image = json_decode($info['image'] , true);
            if(is_array($image)){
                foreach ($image as &$value){
                    $value = imageSetHead($value);
                }
            }
            $info['image'] = $image;
        }
        if(!empty($info['start_time'])) $info['start_time'] = date("Y年m月d日 H时i分" , strtotime($info['start_time']));
        if(!empty($info['end_time'])) $info['end_time'] = date("Y年m月d日 H时i分" , strtotime($info['end_time']));
    }

    private static function checkDataInfo(&$data){
        foreach ($data as &$value){

            if(!empty($value['image'])){
                $value['image'] = json_decode($value['image'] , true);
            }

        }
    }

}