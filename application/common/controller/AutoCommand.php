<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/24
 * Time: 13:58
 */

namespace app\common\controller;


use app\common\model\Activity;
use app\common\model\Draw;
use think\Db;

class AutoCommand
{

    public function appBegin(){

        // 结束活动
        $this->updateActivity();

    }

    // 检查数据并是否有更改并更新
    public function updateActivity(){

        // 获取数据
        $where = [
            ['status','=',1],
            ['end_time','<',date("Y-m-d H:i:s")]
        ];
        $info = Activity::where($where)->order(['id'=>'desc','create_time'=>'desc'])->findOrEmpty()->toArray();

        if($info){
            // 更新抽奖
            Db::startTrans();
            try{
                // 更新抽奖到活动
                $drawWhere = [
                    ['activity_id' , '=' , 0]
                ];
                $ret = Draw::where($drawWhere)->update(['activity_id'=>$info['id'] , 'update_time'=>date("Y-m-d H:i:s")]);
                if(!$ret){
                    Db::rollback();
                    return false;
                }
                // 更新活动状态
                $ret = Activity::where('id',$info['id'])->update(['status'=>2 , 'update_time'=>date("Y-m-d H:i:s")]);
                if(!$ret){
                    Db::rollback();
                    return false;
                }
                Db::commit();
                return true;
            }catch (\Exception $e){
                Db::rollback();
                return false;
            }
        }

        return false;
    }

}