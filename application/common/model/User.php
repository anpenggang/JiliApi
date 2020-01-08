<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/5/24
 * Time: 09:27
 */

namespace app\common\model;


use think\Model;

class User extends Model
{

    public static function checkUser($id=0){
        return self::where('id',$id)->findOrEmpty()->toArray();
    }

    public static function queryData($where = [] , $start = 0 , $limit = 20 , $order = ['create_time'=>'desc']){
        $ret = self::where($where)
            ->limit($start , $limit)
            ->order($order)
            ->select()->toArray();

        return $ret;
    }

    public static function total($where = []){
        return self::where($where)->count();
    }

    public static function queryInfo($where = [] , $order=[]){
        $ret = self::where($where)
            ->order($order)
            ->findOrEmpty()->toArray();

        return $ret;
    }

    public static function queryOneInfo($id=0){
        $info = self::where('id',$id)
            ->field('id,nick_name,cover,phone')
            ->findOrEmpty()->toArray();

        return $info;
    }

    public static function checkPhone($phone=''){
        if(empty($phone)) return false;
        return self::where('phone',$phone)->findOrEmpty()->toArray();
    }

    // 通过微信授权
    // 用户注册或登录
    public static function authUser($info = []){
        if(empty($info['openId'])){
            return [false , "openId不能为空"];
        }
        $openId = isset($info['openId']) ? $info['openId'] : null;

        // 检查该用户是否存在
        $user = self::where('wx_openid' , $openId)->findOrEmpty()->toArray();
        if(!empty($user)){
            return [true , $user];
        }

        // 注册用户
        $create = [
            'wx_openid' => $openId,
            'cover' => isset($info['avatarUrl']) ? $info['avatarUrl'] : "",
            'nick_name' => isset($info['nickName']) ? $info['nickName'] : null,
            'wx_unionid' => isset($info['unionId']) ? $info['unionId'] : null,
            'wx_info' => json_encode($info),
            'create_time' => date("Y-m-d H:i:s"),
            'phone' => isset($info['purePhoneNumber']) ? $info['purePhoneNumber'] : null
        ];
        $user = self::create($create)->toArray();
        if(!$user){
            return [false , '用户注册失败'];
        }
        return [true , $user];
    }

    // 检查用户本日是否已参加活动  true已参加  false未参加
    public static function checkUserJoin($id=0){
        $user = self::where('id' , $id)->findOrEmpty()->toArray();
        if(empty($user)){
            return true;
        }
        $drawTime = $user['draw_time'];
        if($drawTime >= date("Y-m-d 00:00:00") && $drawTime<=date("Y-m-d 23:23:23")){
            return true;
        }

        return false;
    }

    //导出信息
    public static function exportData($activityId=0){

        // 获取活动ID
        $activity = Activity::where('id', $activityId)->findOrEmpty()->toArray();
        $array = [];

        if($activity) {
            $startTime = isset($activity['start_time']) ? $activity['start_time'] : "0000-00-00 00:00:10";
            $endTime = isset($activity['end_time']) ? $activity['end_time'] : "0000-00-00 00:00:10";

            $data = self::whereBetweenTime('draw_time' , $startTime , $endTime)->select()->toArray();

            foreach ($data as $value){

                $nickName = $value['nick_name'];
                $phone = $value['phone'];
                $drawTime = $value['draw_time'];

                // 获取用户的奖品
                $info = WinUser::where([['activity_id','=',$activityId],['user_id','=',$value['id']]])->select()->toArray();
                if($info) {
                    foreach ($info as $valueInfo) {
                        $createTime = $valueInfo['create_time'];
                        $drawStatus = $valueInfo['status'] == 1 ? "已使用" : "待使用";
                        // 获取中奖等级
                        $draw = Draw::where('id', $valueInfo['draw_id'])->findOrEmpty()->toArray();
                        $drawName = isset($draw['name']) ? $draw['name'] : "-";
                        // 获取门店地址
                        $drawStoreAddress = DrawSub::getStore($valueInfo['draw_sub_id']);

                        $array[] = [
                            'nick_name' => $nickName,
                            'phone' => $phone,
                            'draw_time' => $createTime,
                            'draw_name' => $drawName,
                            'draw_store_address' => $drawStoreAddress,
                            'status' => $drawStatus
                        ];
                    }
                }else{
                    $array[] = [
                        'nick_name' => $nickName,
                        'phone' => $phone,
                        'draw_time' => $drawTime,
                        'draw_name' => "-",
                        'draw_store_address' => "-",
                        'status' => "-"
                    ];
                }

            }
        }

        return $array;
    }

    public static function exportUseData($activityId=0){

        $array = [];
        $userList = WinUser::where([['activity_id','=',$activityId],['status','=',1]])->select()->toArray();
        if($userList){
            foreach ($userList as $valueInfo) {
                $createTime = $valueInfo['create_time'];
                $drawStatus = $valueInfo['status'] == 1 ? "已使用" : "待使用";
                // 获取中奖等级
                $draw = Draw::where('id', $valueInfo['draw_id'])->findOrEmpty()->toArray();
                $drawName = isset($draw['name']) ? $draw['name'] : "-";
                // 获取门店地址
                $drawStoreAddress = DrawSub::getStore($valueInfo['draw_sub_id']);
                $user = self::where('id' , $valueInfo['user_id'])->findOrEmpty()->toArray();
                $nickName = isset($user['nick_name']) ? $user['nick_name'] : "";
                $phone = isset($user['phone']) ? $user['phone'] : "";

                $array[] = [
                    'nick_name' => $nickName,
                    'phone' => $phone,
                    'draw_time' => $createTime,
                    'draw_name' => $drawName,
                    'draw_store_address' => $drawStoreAddress,
                    'status' => $drawStatus
                ];
            }
        }

        return $array;
    }
}