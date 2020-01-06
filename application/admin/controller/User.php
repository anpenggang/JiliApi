<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/31
 * Time: 11:08
 * 会员管理
 */

namespace app\admin\controller;


use app\admin\validate\UserValidate;
use app\common\controller\AdminBase;
use think\facade\Request;
use app\common\model\User as UserModal;


class User extends AdminBase
{

    // 用户列表
    public function getUserList(){
        if(Request::isPost()){
            $current = Request::param('current', 1);
            $pageSize = Request::param('pageSize', 20);
            $name = Request::param('name' , '');

            $where = [];
            $current = $current >= 1 ? $current : 1;
            $start = ($current - 1) * $pageSize;
            if(!empty($name)){
                $where[] = ['nick_name','like' , "%{$name}%"];
            }

            $ret = UserModal::queryData($where , $start , $pageSize);
            $total = UserModal::total($where);

            return json(['status' => 'ok', 'message' => '获取成功', 'data' => [
                'list' => $ret,
                'pagination' => [
                    'current' => $current,
                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ]]);
        }

        return json(['status'=>'fail' , 'message'=>'错误的请求方式']);
    }

    // 批量删除和单个数据删除
    public function removeUser(){
        if(Request::isPost()){
            $ids = Request::param('ids' , 0);       // 批量删除
            $id = Request::param('id' , 0);         // 单个删除

            if(!empty($id)){
                $ret = UserModal::where('id' , $id)->delete();
            }else if(!empty($ids) && is_array($ids)){
                $ret = UserModal::where('id' , 'in' , $ids)->delete();
            }

            if(isset($ret) && $ret){
                return json(['status' => 'ok' , 'message' => '删除成功' , 'data' => $ret]);
            }

            return json(['status' => 'fail' , 'message' => '未找到需要删除的数据' , 'data' => ['id' => $id , 'ids' => $ids]]);
        }

        return json(['status' => 'fail' , 'message' => '未知的请求方式']);
    }

}