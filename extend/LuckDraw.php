<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/22
 * Time: 14:58
 *
 * 抽奖计算
 */

class LuckDraw
{

    // 抽奖初始化数据
    public function getReward($data = []){

        $arr = [];
        foreach ($data as $key => $val) {
            if($val['count']>0 && $val['over_count']<=0 ) continue;     // 如果奖品被抽完不在抽奖
            if($val['chance']>0) $arr[$val['id']] = $val['chance'] * 10000;       // 扩大10000倍（最低百万分之一的概率中奖）
        }

        $rid = $this->get_rand($arr); //根据概率获取奖项id

        return $rid;
    }

    // 抽奖
    private function get_rand($proArr) {
        $result = 0;        // 未中奖

        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        if($proSum<=1000000){
            $proSum = 1000000;
        }

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;
    }

}