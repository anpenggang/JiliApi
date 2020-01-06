<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/22
 * Time: 19:12
 */

namespace app\api\controller;


use app\common\controller\PersonnelBase;
use app\common\model\Admin;
use think\facade\Request;
use app\common\model\WinUser as WinUserModel;
use app\common\model\User as UserModel;

class PersonnelUser extends PersonnelBase
{

    // 扫一扫
    public function scan(){
        if(Request::isPost()){
            $code = Request::param('code' , "");
            if(empty($code)){
                return json(['status'=>'fail' , 'message'=>'扫码失败']);
            }

            $winId = \Encode::decrypt($code);

            // 检查信息是否有效
            $info = WinUserModel::queryOne($winId);
            if(empty($info)){
                return json(['status'=>'fail' , 'message'=>'扫码失败']);
            }
            if($info['status']==1){
                return json(['status'=>'fail' , 'message'=>'已使用']);
            }
            if(empty($info['draw'])){
                return json(['status'=>'fail' , 'message'=>'已失效']);
            }
            $time = date("Y-m-d H:i:s");
            if($time<$info['draw']['effective_start_date']){
                return json(['status'=>'fail' , 'message'=>'未到使用日期哦！']);
            }
            if($time>$info['draw']['effective_end_date']){
                return json(['status'=>'fail' , 'message'=>'已过期']);
            }

            $ret = WinUserModel::where([['id','=',$winId],['status','=',0]])->update(['status'=>1 , 'personnel_id'=>$this->id , 'update_time'=>date("Y-m-d H:i:s")]);
            if(!$ret){
                return json(['status'=>$ret , 'message'=>'扫码失败 error 4005']);
            }
            $data = WinUserModel::queryOne($winId);
            return json(['status'=>'ok' , 'message'=>'使用成功' , 'data'=>$data]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 我的验票记录
    public function getCheckTicketList(){
        if(Request::isPost()){
            $current = Request::param('current', 1);
            $pageSize = Request::param('pageSize', 20);

            $where = [];
            $current = $current >= 1 ? $current : 1;
            $start = ($current - 1) * $pageSize;
            $where[] = ['personnel_id','=',$this->id];

            $list = WinUserModel::queryData($where , $start , $pageSize , ['update_time'=>'desc']);
            $total = WinUserModel::total($where);

            return json(['status' => 'ok', 'message' => '获取成功', 'data' => [
                'list' => $list,
                'pagination' => [
                    'current' => $current,
                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ]]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 用户信息
    public function info(){
        $info = Admin::checkUser($this->id);

        return json(['status'=>'ok' , 'message'=>'成功' , 'data'=>$info]);
    }
}