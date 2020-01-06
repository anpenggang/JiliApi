<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 15:31
 */

namespace app\common\model;


use think\Model;

class Draw extends Model
{

    public static function queryData($where = [] , $start = 0 , $limit = 20 , $order = ['create_time'=>'desc']){
        $ret = self::where($where)
            ->limit($start , $limit)
            ->order($order)
            ->select()->toArray();

        foreach ($ret as &$value){
            self::checkDataInfo($value);
        }

        return $ret;
    }

    public static function total($where = []){
        $total = self::where($where)->count();
        return $total;
    }

    // 获取转盘数据
    public static function queryTurntable(){
        $where = [
            ['activity_id' , '=' , 0]
        ];
        $list = self::where($where)->field("id,name,content,count")->select()->toArray();

        $isNo = false;
        foreach ($list as &$value){
            $value['count'] = DrawSub::where('draw_id' , $value['id'])->sum('count');
            if(empty($value['count'])) $isNo = true;
        }
        if(!$isNo){
            $list[] = ['id'=>0,'name'=>'谢谢参与', 'content'=>'谢谢参与','count'=>0];
        }

        return $list;
    }
    public static function queryAllTurntable(){
        $where = [
            ['activity_id' , '=' , 0]
        ];
        $list = self::where($where)->select()->toArray();

        return $list;
    }



    // 获取抽奖数组 (中奖列表不包含谢谢参与)
    public static function queryLuckDraw(){
        $where = [
            ['activity_id' , '=' , 0]
        ];
        $list = self::where($where)->field("id,name,content")->select()->toArray();
        foreach ($list as $key => $value){
            $count = DrawSub::where('draw_id' , $value['id'])->sum('count');
            if($count<=0){
                unset($list[$key]);
            }
        }

        return $list;
    }

    // 获取谢谢惠顾ID
    public static function queryNo(){
        $where = [
            ['activity_id' , '=' , 0]
        ];
        $order = ['create_time'=>'desc'];
        $list = self::where($where)->field('id')->order($order)->select()->toArray();
        foreach ($list as $key => $value){
            $count = DrawSub::where('draw_id' , $value['id'])->sum('count');
            if($count<=0){
                return empty($value['id']) ? 0 : $value['id'];
            }
        }
        return 0;
    }

    // 获取奖品信息
    public static function queryOne($id = 0){

        $info = self::where('id' , $id)->findOrEmpty()->toArray();
        self::checkInfo($info);
        return $info;

    }

    private static function checkInfo(&$info){
        if(!empty($info['effective_start_time'])) {
            $info['effective_start_date'] = $info['effective_start_time'];
            $info['effective_start_time'] = date("Y年m月d日 H时i分" , strtotime($info['effective_start_time']));
        }

        if(!empty($info['effective_end_time'])) {
            $info['effective_end_date'] = $info['effective_end_time'];
            $info['effective_end_time'] = date("Y年m月d日 H时i分" , strtotime($info['effective_end_time']));
        }
    }

    private static function checkDataInfo(&$info){

        $draw = DrawSub::where('draw_id' , $info['id'])
            ->field("id,count,name,address,chance,over_count")
            ->select()->toArray();
        $info['sub_list'] = $draw;
        $info['count'] = DrawSub::where('draw_id' , $info['id'])->sum('count');
        $info['chance'] = DrawSub::where('draw_id' , $info['id'])->sum('chance');
        $info['chance'] = round($info['chance'] , 4);

    }
}