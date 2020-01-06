<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/6/4
 * Time: 10:56
 */

namespace app\common\model;


use think\Model;

class Admin extends Model
{

    // type  1前端工作人员 2后台管理人员

    // 后台管理员登录检查
    public static function checkAdminLogin($userName , $password){

        $user = self::where(['type'=>2 , 'account' => $userName , 'password' => encryptPass($password)])
            ->findOrEmpty()->toArray();

        return $user?:false;
    }

    // 前端工作人员登录检查
    public static function checkWorkLogin($userName , $password){

        $user = self::where(['type'=>1 , 'account' => $userName , 'password' => encryptPass($password)])
            ->findOrEmpty()->toArray();

        return $user?:false;
    }

    // 获取指定用户信息
    public static function checkUser($id = 0){
        $user = self::where([['id',"=",$id]])
            ->findOrEmpty()->toArray();

        if(isset($user['account'])){
            $user['name'] = $user['account'];
        }

        if(isset($user['password'])){
            unset($user['password']);
        }

        return $user;
    }

    // 后台检查登录
    public static function checkAdminIsNormal($id = 0) {
        $ret = self::where(['id'=>$id , 'type'=>2])
            ->findOrEmpty()->toArray();
        return $ret ?: false;
    }

    //  前端检查登录
    public static function checkWorkIsNormal($id = 0) {
        $ret = self::where(['id'=>$id , 'type'=>1])
            ->findOrEmpty()->toArray();
        return $ret ?: false;
    }


    public static function queryData($where = [] , $start=0 , $limit=20 , $order=['create_time'=>'desc']){
        $ret = self::where($where)
            ->limit($start , $limit)
            ->order($order)
            ->select()->toArray();

        foreach ($ret as &$value){
            $value['roles'] = !empty($value['roles']) ? json_decode($value['roles']) : '';
        }

        return $ret;
    }

    public static function total($where = []){
        return self::where($where)->count();
    }

    public static function queryDataAll($where = []){
        $ret = self::where($where)
            ->field("id,user_name as name")
            ->select()->toArray();

        return $ret;
    }

    /**
     * 检查账号是否已存在
     * @param string $name 账号
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkUserName($name = "" , $id = 0){
        $ret = self::where([['account','=',$name] , ['id',"<>",$id]])->findOrEmpty()->toArray();
        return $ret?true:false;
    }

    // 后台左侧菜单信息
    public static function menuData(){
        // 用户管理
        $userMenu = [
            "path" => "/account_manage/user",
            "name" => "用户管理",
            "icon" => "user",
            "exact" => true
        ];
        // 活动管理
        $activityMenu = [
            "path" => "/activity_hand",
            "name" => "已失效活动管理",
            "icon" => "contacts",
            "exact" => true
        ];
        // 活动管理
        $activityNormalMenu = [
            "path" => "/activity_normal",
            "name" => "发布中活动管理",
            "icon" => "contacts",
            "exact" => true
        ];
        // 抽奖管理
        $drawMenu = [
            "path" => "/draw",
            "name" => "抽奖管理",
            "icon" => "contacts",
            "children" => [
                [
                    "path" => "/draw/set",
                    "name" => "抽奖设置",
                    "exact" => true
                ],[
                    "path" => "/draw/user_list",
                    "name" => "中奖用户列表",
                    "exact" => true
                ],[
                    "path" => "/draw/use_list",
                    "name" => "奖品使用情况",
                    "exact" => true
                ],
            ]
        ];
        // 题目管理
        $questionMenu = [
            "path" => "/question",
            "name" => "题目管理",
            "icon" => "contacts",
            "exact" => true
        ];
        // 账号管理
        $accountMenu = [
            "path" => "/a_manage",
            "name" => "账号管理",
            "icon" => "contacts",
            "exact" => true
        ];
        // 账号安全
        $resetMenu = [
            "path" => "/reset_pass",
            "name" => "账号安全",
            "icon" => "unlock",
            "exact" => true
        ];

        return [$userMenu,$activityMenu,$activityNormalMenu,$drawMenu,$questionMenu,$accountMenu,$resetMenu];
    }

    // 获取菜单
    public static function getMenu($role_type=0 , $roles=[]){

        $menu = [];
        if($role_type == 1){
            // 超管权限
            $menu = self::menuData();
        }
        else if($role_type == 2 && !empty($roles) && is_array($roles)){
            // 普通管理员
//            list($userMenu,$activityMenu,$activityNormalMenu,$drawMenu,$questionMenu,$accountMenu,$resetMenu) = self::menuData();
//            foreach ($roles as $value){
//                switch ($value){
//                    case "user": $menu[]=$userMenu; break;
//                }
//            }
//            $menu[] = $resetMenu;

            $menu = self::menuData();
        }

        return $menu;
    }
}