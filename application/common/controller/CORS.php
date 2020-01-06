<?php
/**
 * 跨域请求
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/24
 * Time: 09:33
 */

namespace app\common\controller;


class CORS
{
    public function appInit(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: token, Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header('Access-Control-Allow-Methods: POST,GET,PUT,DELETE');

        if(request()->isOptions()){
            exit();
        }
    }
}