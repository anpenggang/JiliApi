<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/8/5
 * Time: 18:47
 */

namespace app\api\controller;


use app\api\validate\UserInfoValidate;
use app\common\controller\UserBase;
use think\facade\Request;
use app\common\model\User as UserModel;
use app\common\model\WinUser as WinUserModel;

class User extends UserBase
{

    // 设置新的电话号码
    public function setNewPhone(){
        if(Request::isPost()){
            $phone = Request::param('phone' , '');
            $code = Request::param('code' , '');

            $validate = new UserInfoValidate();
            if(!$validate->sceneCheckPhoneCode()->check(Request::param())){
                return json(['status'=>'fail' , 'message'=>$validate->getError() , 'data'=>Request::param()]);
            }

            $phoneCode = cache($phone);
            if ($phoneCode != $code) {
                return json(['status' => 'fail', 'message' => '验证码错误']);
            }

            // 检查电话号码是否已注册
            $user = UserModel::checkPhone($phone);
            if($user){
               return json(['status'=>'fail' , 'message'=>'电话号码已存在']);
            }

            // 更新电话号码
            $userRet = UserModel::update(['id'=>$this->id , 'phone'=>$phone]);
            if(!$userRet){
                return json(['status'=>'fail' , 'message'=>'更新失败']);
            }
            return json(['status'=>'ok' , 'message'=>'更新成功']);
        }

        return json(['status'=>'fail' , 'message'=>'未知的请求方式']);
    }

    // 更新用户信息
    public function updateInfo(){
        if(Request::isPost()){
            $param = Request::param();

            $update = [];
            if(isset($param['cover'])) $update['cover'] = $param['cover'];
            if(isset($param['nick_name'])) $update['nick_name'] = $param['nick_name'];

            if(empty($update)){
                return json(['status'=>'fail' , 'message'=>"无更新"]);
            }
            $update['id'] = $this->id;
            $userRet = UserModel::update($update);
            if(!$userRet){
                return json(['status'=>'fail' , 'message'=>'更新失败' , 'param'=>$update]);
            }
            return json(['status'=>'ok' , 'data'=>$userRet , 'message'=>'更新成功'  , 'param'=>$update]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 用户信息
    public function info(){
        if(Request::isPost()){
            $info = UserModel::queryOneInfo($this->id);

            isset($info['cover']) ? $info['cover'] = imageSetHead($info['cover']) : null ;

            return json(['status'=>'ok' , 'message'=>'成功' , 'data'=>$info]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 中奖记录（我的卡包）列表
    public function getWinList(){
        if(Request::isPost()) {
//            $current = Request::param('current', 1);
//            $pageSize = Request::param('pageSize', 20);
            $type = Request::param('type' , '');        // 我的卡包（card） 中奖记录（record）

            $where = [];
//            $current = $current >= 1 ? $current : 1;
//            $start = ($current - 1) * $pageSize;
            $where[] = ['user_id', '=', $this->id];
            if($type==="card"){
                $where[] = ['status','=',0];
                $where[] = ['personnel_id','=',0];
            }

            $info = WinUserModel::queryAllData($where);
            $total = WinUserModel::total($where);


            return json(['status' => 'ok', 'message' => '获取成功', 'data' => [
                'list' => $info,
                'pagination' => [
//                    'current' => $current,
//                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ]]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 未设置更新用户信息
    public function noSetUpdateInfo(){
        if(Request::isPost()){
            $param = Request::param();

            $update = [];
            if(isset($param['cover']) && empty($this->user['cover'])) $update['cover'] = $param['cover'];
            if(isset($param['nick_name']) && empty($this->user['nick_name'])) $update['nick_name'] = $param['nick_name'];

            if(empty($update)){
                return json(['status'=>'fail' , 'message'=>"无更新"]);
            }
            $update['id'] = $this->id;
            $userRet = UserModel::update($update);
            if(!$userRet){
                return json(['status'=>'fail' , 'message'=>'更新失败' , 'param'=>$update]);
            }
            return json(['status'=>'ok' , 'data'=>$userRet , 'message'=>'更新成功'  , 'param'=>$update]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }


}