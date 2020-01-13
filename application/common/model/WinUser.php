<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 16:59
 *
 * 中奖用户列表
 */

namespace app\common\model;


use think\Model;

class WinUser extends Model
{

    public static function queryData($where = [] , $start = 0 , $limit = 20 , $order = ['create_time'=>'desc']){
        $ret = self::where($where)
            ->limit($start , $limit)
            ->order($order)
            ->select()->toArray();

        foreach ($ret as &$value){
            self::checkInfo($value);
        }

        return $ret;
    }

    public static function queryAllData($where = [] , $order = ['create_time'=>'desc']){
        $ret = self::where($where)
//            ->limit($start , $limit)
            ->order($order)
            ->select()->toArray();

        foreach ($ret as &$value){
            self::checkInfo($value);
        }

        return $ret;
    }

    public static function total($where = []){
        $total = self::where($where)->count();
        return $total;
    }

    // 获取奖品信息
    public static function queryOne($id = 0){
        $info = self::where('id' , $id)->findOrEmpty()->toArray();

        self::checkInfo($info);

        return $info;
    }

    private static function checkInfo(&$info){
        if(!empty($info['draw_id'])){
            $draw = Draw::queryOne($info['draw_id']);

            $info['draw'] = [
                'name' => isset($draw['name'])?$draw['name']:"",
                'content' => isset($draw['content'])?$draw['content']:"",
                'effective_start_time' => isset($draw['effective_start_time']) ? $draw['effective_start_time'] : "",
                'effective_end_time' => isset($draw['effective_end_time']) ? $draw['effective_end_time'] : "",
                'effective_start_date' => isset($draw['effective_start_date']) ? $draw['effective_start_date'] : "",
                'effective_end_date' => isset($draw['effective_end_date']) ? $draw['effective_end_date'] : "",
                'store' => isset($info['draw_sub_id']) ? DrawSub::getStore($info['draw_sub_id']) : 0,
                'remark' => isset($draw['remark']) ? $draw['remark'] : "",
            ];

            if (strtotime($draw['effective_end_date']) < strtotime(date('Y-m-d H:i:s'))) {
                $info['status'] = 2; //0：未使用，1：已使用，2：已过期
            }
        }
        if(!empty($info['activity_id'])){
            $activity = Activity::queryOne($info['activity_id']);
            $info['activity'] = [
                'store' => isset($activity['store']) ? $activity['store'] : null,
                'title' => isset($activity['title']) ? $activity['title'] : null,
            ];
        }
        if(!empty($info['user_id'])){
            $user = User::queryOneInfo($info['user_id']);
            $info['user'] = [
                'nick_name' => isset($user['nick_name']) ? $user['nick_name'] : null,
                'phone' => isset($user['phone']) ? $user['phone'] : null,
                'cover' => isset($user['cover']) ? $user['cover'] : null
            ];
        }
        if(!empty($info['id'])){
            $info['code'] = \Encode::encrypt($info['id']);          // 二维码字符串
        }
    }

}