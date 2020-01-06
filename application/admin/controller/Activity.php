<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 11:20
 * 活动管理
 */

namespace app\admin\controller;


use app\admin\validate\ActivityValidate;
use app\common\controller\AdminBase;
use app\common\controller\Upload;
use think\facade\Request;
use app\common\model\Activity as ActivityModel;
use app\common\model\Draw as DrawModel;

class Activity extends AdminBase
{

    // 获取活动列表
    // type = 1活动中  type = 2 已失效
    public function getList(){
        if(Request::isPost()){

            $current = Request::param('current', 1);
            $pageSize = Request::param('pageSize', 20);
            $type = Request::param('type' , '');

            $where = [];
            $current = $current >= 1 ? $current : 1;
            $start = ($current - 1) * $pageSize;
            switch ($type){
                case "hand":$where[] = ['status','=',1];break;  // 活动中
                case "over":$where[] = ['status','=',2];break;  // 已失效
                default:break;  // 不区分(默认)
            }

            $ret = ActivityModel::queryData($where , $start , $pageSize);
            $total = ActivityModel::total($where);

            switch ($type){
                case "hand":
                    foreach ($ret as &$value){
                        $value['draw'] = DrawModel::queryData([['activity_id','=',0]] , 0,50,['create_time'=>'asc']);
                    }
                    break;
                case "over":
                    foreach ($ret as &$value){
                        $value['draw'] = DrawModel::queryData([['activity_id','=',$value['id']]] , 0,50,['create_time'=>'asc']);
                    }
                    break;
            }

            return json(['status' => 'ok', 'message' => '获取成功', 'data' => [
                'list' => $ret,
                'pagination' => [
                    'current' => $current,
                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ]]);

        }
        return json(['status'=>'fail' , 'message'=>'未知的请求方式']);
    }

    // 创建(编辑)活动
    public function createActivity(){
        if(Request::isPost()){
            $id = Request::param('id' ,0);
            $data['title'] = Request::param('title' , '');
            $data['subtitle'] = Request::param('subtitle' , '');
            $data['content'] = Request::param('content' , '');
            $startEndTime = Request::param('startEndTime' , '');
            $time = timeConversion($startEndTime);
            $data['start_time'] = empty($time[0]) ? '' : $time[0];
            $data['end_time'] = empty($time['1']) ? '' : $time[1];
            $store = Request::param('store' , '');
            $data['store'] = $store;
            $data['image'] = Request::param('cover' , '');
            $data['image'] = json_encode($data['image']);

            $validate = new ActivityValidate();
            if(!$validate->sceneCreateEditActivity()->check($data)){
                return json(['status'=>'fail' , 'message'=>$validate->getError() , 'data'=>$data]);
            }

            if(empty($id)){
                // 检查活动是否已创建
                $activity = ActivityModel::checkOneActivity();
                if($activity){
                    return json(['status'=>'fail' , 'message'=>"你已存在有效活动，不可同时存在多个"]);
                }

                // 创建
                $data['create_time'] = date("Y-m-d H:i:s");
                $ret = ActivityModel::create($data)->toArray();
            }else{
                // 编辑
                $data['id'] = $id;
                $data['update_time'] = date("Y-m-d H:i:s");
                $ret = ActivityModel::update($data)->toArray();
            }

            if(!$ret){
                return json(['status'=>'fail' , 'message'=>'操作失败' , 'data'=>$ret]);
            }
            return json(['status'=>'ok' , 'message'=>'操作成功' , 'data'=>$ret]);
        }

        return json(['status'=>'fail' , 'message'=>'未知的请求方式']);
    }

    // 删除活动
    public function removeActivity(){
        if(Request::isPost()){
            $ids = Request::param('ids' , 0);       // 批量删除
            $id = Request::param('id' , 0);         // 单个删除

            if(!empty($id)){
                $ret = ActivityModel::where('id' , $id)->delete();
            }else if(!empty($ids) && is_array($ids)){
                $ret = ActivityModel::where('id' , 'in' , $ids)->delete();
            }

            if(isset($ret) && $ret){
                return json(['status' => 'ok' , 'message' => '删除成功' , 'data' => $ret]);
            }

            return json(['status' => 'fail' , 'message' => '未找到需要删除的数据' , 'data' => ['id' => $id , 'ids' => $ids]]);
        }

        return json(['status' => 'fail' , 'message' => '未知的请求方式']);
    }

    // 导出
    public function exportUserInfo(){
        $head = [
            [
                'type' => 'nick_name',
                'name' => '用户昵称',
                'col' => 'A'
            ],[
                'type' => 'phone',
                'name' => '用户联系方式',
                'col' => 'B'
            ],[
                'type' => 'draw_time',
                'name' => '中奖时间',
                'col' => 'C'
            ],[
                'type' => 'draw_name',
                'name' => '中奖等级',
                'col' => 'D'
            ],[
                'type' => 'draw_store_address',
                'name' => '中奖门店(地址)',
                'col' => 'E'
            ],[
                'type' => 'status',
                'name' => '奖券使用情况',
                'col' => 'F'
            ],

        ];
        $filename = "user-excel.xlsx";

        // 获取最后一个活动
        $activity = ActivityModel::where([])->field('id')->order(['create_time'=>'desc' , 'id'=>'desc'])->findOrEmpty()->toArray();
        $activityId = !empty($activity) && isset($activity['id']) ? $activity['id'] : 0;

        $data = \app\common\model\User::exportData($activityId);

        $Upload = new Upload();
        $Upload->exportExcel($head , $filename , $data);
    }

    // 导出已使用奖券
    public function exportUseInfo(){
        $head = [
            [
                'type' => 'nick_name',
                'name' => '用户昵称',
                'col' => 'A'
            ],[
                'type' => 'phone',
                'name' => '用户联系方式',
                'col' => 'B'
            ],[
                'type' => 'draw_time',
                'name' => '中奖时间',
                'col' => 'C'
            ],[
                'type' => 'draw_name',
                'name' => '中奖等级',
                'col' => 'D'
            ],[
                'type' => 'draw_store_address',
                'name' => '中奖门店(地址)',
                'col' => 'E'
            ],[
                'type' => 'status',
                'name' => '奖券使用情况',
                'col' => 'F'
            ],

        ];
        $filename = "user-excel.xlsx";

        $id = Request::param("id" , 0);
        // 获取最后一个活动
        $activity = ActivityModel::where([])->field('id')->order(['create_time'=>'desc' , 'id'=>'desc'])->findOrEmpty()->toArray();
        $activityId = !empty($activity) && isset($activity['id']) ? $activity['id'] : 0;

        // 如果设置了自定义则使用自定义
        if(!empty($id)) $activityId = $id;

        $data = \app\common\model\User::exportUseData($activityId);

        $Upload = new Upload();
        $Upload->exportExcel($head , $filename , $data);
    }
}