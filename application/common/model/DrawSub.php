<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/29
 * Time: 14:38
 */

namespace app\common\model;


use think\Model;

class DrawSub extends Model
{

    // 获取店铺名称和地址
    public static function getStore($id = ""){
        $ret = self::where('id' , $id)->field('name,address')->findOrEmpty()->toArray();
        if($ret){
            return $ret['name']."（".$ret['address']."）";
        }
        return "";
    }

}