<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/5/9
 * Time: 15:22
 */

namespace app\admin\controller;

use think\Controller;
use think\facade\Request;

class Index extends Controller
{

    // 图片上传
    public function upload(){

        $file = $this->request->file('file');
        $type = Request::param('type');
        $fileUp = $file->move('./static/upload');
        $data = [
            'size' =>$fileUp->getSize(),
            'fileName' => $fileUp->getFilename(),
            'saveName' => $fileUp->getSaveName(),
            'url' => '/static/upload/'.$fileUp->getSaveName(),
        ];

        return json(['data' => $data , 'status' => "ok"]);

    }

    // 文件上传
    public function uploadFile(){
        $file = $this->request->file('file');
        $fileUp = $file->move('./upload/folder');
        $data = [
            'size' =>$fileUp->getSize(),
            'fileName' => $fileUp->getFilename(),
            'saveName' => $fileUp->getSaveName(),
            'url' => '/upload/folder/'.$fileUp->getSaveName(),
        ];

        return json(['data' => $data , 'status' => "ok"]);
    }

    protected function getTypeName($type=""){
        switch ($type){
            case "":break;
            default:break;
        }
    }

}