<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 14:20
 *
 * 试题（题目）管理
 */

namespace app\admin\controller;


use app\admin\validate\QuestionValidate;
use app\common\controller\AdminBase;
use think\facade\Request;
use app\common\model\Questions as QuestionsModel;

class Question extends AdminBase
{

    // 题目列表
    public function getList(){
        if(Request::isPost()){
            $current = Request::param('current', 1);
            $pageSize = Request::param('pageSize', 20);

            $where = [];
            $current = $current >= 1 ? $current : 1;
            $start = ($current - 1) * $pageSize;

            $ret = QuestionsModel::queryData($where , $start , $pageSize);
            $total = QuestionsModel::total($where);

            return json(['status' => 'ok', 'message' => '获取成功', 'data' => [
                'list' => $ret,
                'pagination' => [
                    'current' => $current,
                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ]]);
        }

        return json(['status'=>'fail' , 'message'=>'错误的请求方式']);
    }

    // 添加题目
    public function createQuestion(){

        if(Request::isPost()){
            $data['option_a'] = Request::param('option_a' , '');
            $data['option_b'] = Request::param('option_b' , '');
            $data['option'] = Request::param('option' , '');
            $data['subject'] = Request::param('subject' , '');
            $id = Request::param('id' , 0);

            $validate = new QuestionValidate();
            if(!$validate->sceneCreateEditQuestion()->check($data)){
                return json(['status'=>'fail' , 'message'=>$validate->getError()]);
            }

            if(empty($id)){
                // 创建题目
                $data['create_time'] = date("Y-m-d H:i:s");
                $ret = QuestionsModel::create($data)->toArray();
            }else{
                // 编辑题目
                $data['id'] = $id;
                $data['update_time'] = date("Y-m-d H:i:s");
                $ret = QuestionsModel::update($data)->toArray();
            }

            if(!$ret){
                return json(['status'=>'fail' , 'message'=>'操作失败' , 'data'=>$ret]);
            }
            return json(['status'=>'ok' , 'message'=>'操作成功' , 'data'=>$ret]);
        }

        return json(['status'=>'fail' , 'message'=>'错误的请求方式']);
    }

    // 删除题目
    public function removeQuestion(){
        if(Request::isPost()){
            $ids = Request::param('ids' , 0);       // 批量删除
            $id = Request::param('id' , 0);         // 单个删除

            if(!empty($id)){
                $ret = QuestionsModel::where('id' , $id)->delete();
            }else if(!empty($ids) && is_array($ids)){
                $ret = QuestionsModel::where('id' , 'in' , $ids)->delete();
            }

            if(isset($ret) && $ret){
                return json(['status' => 'ok' , 'message' => '删除成功' , 'data' => $ret]);
            }

            return json(['status' => 'fail' , 'message' => '未找到需要删除的数据' , 'data' => ['id' => $id , 'ids' => $ids]]);
        }

        return json(['status' => 'fail' , 'message' => '未知的请求方式']);
    }

}