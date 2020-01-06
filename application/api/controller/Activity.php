<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 18:05
 */

namespace app\api\controller;


use app\common\controller\UserBase;
use app\common\model\WinUser;
use think\Db;
use think\facade\Request;
use app\common\model\Activity as ActivityModel;
use app\common\model\Questions as QuestionsModel;
use app\common\model\AnswerRecord as AnswerRecordModel;
use app\common\model\Draw as DrawModel;
use app\common\model\DrawSub as DrawSubModel;
use app\common\model\WinUser as WinUserModel;
use app\common\model\User as UserModel;

class Activity extends UserBase
{

    // 获取活动
    public function getActivity(){
        if(Request::isPost()){
            $data = ActivityModel::getOneActivity();

            if(!$data){
                return json(['status'=>'fail' , 'message'=>'无有效的活动']);
            }

            // 检查用户是否已参加活动
            $status = UserModel::checkUserJoin($this->id);
            $data['is_join'] = $status ? 2 : 1;   // 1可参加 2已参加

            return json(['status'=>'ok' , 'message'=>'获取成功' , 'data'=>$data]);

        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 获取题目
    // 随机抽取5道题
    public function getQuestionList(){
        $data = QuestionsModel::getQuestion();      // 默认随机抽取5道题

        if(empty($data)){
            return json(['status'=>'fail' , 'message'=>'未获取到试题' , 'data'=>$data]);
        }

        return json(['status'=>'ok' , 'message'=>'获取成功' , 'data'=>['list'=>$data]]);
    }

    // 提交试题答案
    public function applyQuestion(){
        if(Request::isPost()){
            $answer = Request::param('answer' , []);    // 答案列表['id'=>1] 1=A 2=B
            $activityId = Request::param('activity_id' , 0);

            // 检查活动是否存在
            $activity = ActivityModel::checkActivity($activityId);
            if(!$activity){
                return json(['status'=>'fail' , 'message'=>'活动异常']);
            }

            // 验证答案（获取分数）
             list($number , $data) = QuestionsModel::checkNumber($answer);
            //  $number = 1; $data = [];
            // 创建答题记录
            $ret = AnswerRecordModel::create([
                'user_id'=>$this->id ,
                'subject_info'=>json_encode($answer),
                'subject_number'=>$number ,
                'activity_id'=>$activityId ,
                'create_time'=>date("Y-m-d H:i:s")
            ])->toArray();
            if(!$ret || empty($ret['id'])){
                return json(['status'=>'fail' , 'message'=>'答题提交失败！']);
            }
            $data['id'] = $ret['id'];       // 答题记录ID

            if($number<0.8){
                // 未通过
                $data['adopt'] = false; // 未通过
                return json(['status'=>'ok' , 'message'=>'未通过答题' , 'data'=>$data]);
            }
            // 通过
            $data['adopt'] = true;      // 通过
            return json(['status'=>'ok' , 'message'=>'通过答题' , 'data'=>$data , 'info'=>$answer]);

        }
        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 获取抽奖列表
    public function getLuckDrawList(){
        if(Request::isPost()){
            $id = Request::param('id' , 0);

            list($status , $message , $info) = AnswerRecordModel::checkAnswer($id);
            if(!$status){
                return json(['status'=>'fail' , 'message'=>$message , 'data'=>$info]);
            }

            // 检查活动是否存在
            $activityId = $info['activity_id'];
            $activity = ActivityModel::checkActivity($activityId);
            if(!$activity){
                return json(['status'=>'fail' , 'message'=>'活动异常']);
            }

            // 奖项设置
            $data['list_set'] = DrawModel::queryLuckDraw();
            // 转盘信息
            $data['list'] = DrawModel::queryTurntable();

            return json(['status'=>'ok' , 'message'=>'获取成功' , 'data'=>$data]);
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    // 进行抽奖
    public function luckDraw(){
        if(Request::isPost()){
            $id = Request::param('id' , 0);

            // 检查题抽奖情况
            list($status , $message , $info) = AnswerRecordModel::checkAnswer($id);
            if(!$status){
                return json(['status'=>'fail' , 'message'=>$message , 'data'=>$info]);
            }
            // 检查用户抽奖情况
            if(UserModel::checkUserJoin($this->id)){
                return json(['status'=>'fail' , 'message'=>"今日你已抽奖哦"]);
            }

            $activityId = isset($info['activity_id']) ? $info['activity_id'] : 0;
            // 计算抽奖结果
            $listDraw = DrawModel::queryAllTurntable();
            $listIds = array_column($listDraw , 'id');
            $list = DrawSubModel::where('draw_id' , 'in' ,$listIds)->select()->toArray();

            // 获取到抽奖结果ID
            $luckDraw = new \LuckDraw();
            $dSubId = $luckDraw->getReward($list);
            $dId = 0;
            if(!empty($dSubId)) {
                // 获取奖项ID
                $drawSubOne = DrawSubModel::where('id', $dSubId)->findOrEmpty()->toArray();
                if (empty($drawSubOne)) {
                    return json(['status' => 'fail', 'message' => '抽奖异常']);
                }
                $dId = $drawSubOne['draw_id'];
            }

            $nId = DrawModel::queryNo();
            Db::startTrans();
            try{
                if( !empty($dId) && $nId!=$dId ) {
//                    // 扣除奖品数量
//                    $ret = DrawModel::where([['id', '=', $dId], ['over_count', '>=', 1]])->setDec('over_count', 1);
//                    if (!$ret) {
//                        Db::rollback();
//                        return json(['status' => 'fail', 'message' => '抽奖失败 ERROR 11001', 'data' => ['id' => $nId]]);
//                    }
                    // 扣除奖项数量
                    $ret = DrawSubModel::where([['id', '=', $dSubId], ['over_count', '>=', 1]])->setDec('over_count', 1);
                    if (!$ret) {
                        Db::rollback();
                        return json(['status' => 'fail', 'message' => '抽奖失败 ERROR 11001', 'data' => ['id' => $nId]]);
                    }
                    // 生成中奖记录
                    $ret = WinUserModel::create([
                        'user_id' => $this->id,
                        'draw_id' => $dId,
                        'draw_sub_id' => $dSubId,
                        'activity_id' => $activityId,
                        'create_time' => date("Y-m-d H:i:s"),
                        'status' => 0
                    ])->toArray();
                    if (!$ret) {
                        Db::rollback();
                        return json(['status' => 'fail', 'message' => '抽奖失败 ERROR 11002', 'data' => ['id' => $nId]]);
                    }
                    $winId = isset($ret['id']) ? $ret['id'] : 0;
                }

                // 标记用户已抽奖
                $ret = UserModel::where([['id', '=', $this->id]])
                    ->whereNotBetween('draw_time', date("Y-m-d 00:00:00") . ',' . date("Y-m-d 23:23:23"))
                    ->update(['draw_time' => date("Y-m-d H:i:s")]);
                if (!$ret) {
                    Db::rollback();
                    return json(['status' => 'fail', 'message' => '抽奖失败 ERROR 11003', 'info'=>$ret , 'data' => ['id' => $nId]]);
                }

                // 标记答题记录为已抽奖
                $ret = AnswerRecordModel::where([['id', '=', $id], ['status', '=', 0]])->update(['status' => 1]);
                if (!$ret) {
                    Db::rollback();
                    return json(['status' => 'fail', 'message' => '抽奖失败 ERROR 11004', 'data' => ['id' => $nId]]);
                }

                Db::commit();
                $dataInfo = ['id'=>$dId];
                if(!empty($winId)) $dataInfo['win_id'] = $winId;
                return json(['status'=>'ok' , 'message'=>'抽奖成功' , 'data'=> $dataInfo ]);
            }catch (\Exception $e){
                Db::rollback();
                return json(['status'=>'fail' , 'message'=>'抽奖异常' , 'data'=>$e->getMessage()]);
            }
        }

        return json(['status'=>'fail' , 'error'=> '错误的请求方式']);
    }

    //

    // 获取奖品信息
    public function getWinInfo(){
        if(Request::isPost()){
            $winId = Request::param('win_id' , 0);

            $info = WinUserModel::queryOne($winId);

            if(empty($info)){
                return json(['status'=>'fail' , 'message'=>'获取失败']);
            }
            return json(['status'=>'ok' , 'message'=>'获取成功' , 'data'=>$info]);
        }
    }


}