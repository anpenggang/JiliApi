<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/10/21
 * Time: 15:01
 *
 * 抽奖设置
 */

namespace app\admin\controller;


use app\admin\validate\DrawSubValidate;
use app\admin\validate\DrawValidate;
use app\common\controller\AdminBase;
use think\Db;
use think\facade\Request;
use app\common\model\Draw as DrawModel;
use app\common\model\DrawSub as DrawSubModel;
use app\common\model\WinUser as WinUserModel;

class Draw extends AdminBase
{

    // 抽奖设置列表
    public function getList(){
        if(Request::isPost()){

            $current = Request::param('current', 1);
            $pageSize = Request::param('pageSize', 20);

            $where = [];
            $current = $current >= 1 ? $current : 1;
            $start = ($current - 1) * $pageSize;
            $where[] = ['activity_id','=',0];

            $ret = DrawModel::queryData($where , $start , $pageSize , ['create_time'=>'asc']);
            $total = DrawModel::total($where);

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

    // 添加抽奖
    public function createDraw(){
        if(Request::isPost()){

            $data['name'] = Request::param('name' , '');
            $startEndTime = Request::param('startEndTime' , '');
            $time = timeConversion($startEndTime);
            $data['effective_start_time'] = empty($time[0]) ? '' : $time[0];
            $data['effective_end_time'] = empty($time[1]) ? '' : $time[1];
            $data['remark'] = Request::param('remark' , '');
            $data['content'] = Request::param('content' , '');
            $subList = Request::param('subList' , []);
            $subRemoveList = Request::param('subRemoveList' , []);
            $id = Request::param('id' , 0);

            $validate = new DrawValidate();
            if(!$validate->sceneCreateEditDraw()->check($data)){
                return json(['status'=>'fail' , 'message'=>$validate->getError() , 'data'=>$data]);
            }

            if(empty($subList)){
                return json(['status'=>'fail' , 'message'=>"奖券信息不能为空"]);
            }

            $subValidate = new DrawSubValidate();
            foreach ($subList as $value) {
                if (!$subValidate->sceneCreateEditDraw()->check($value)) {
                    return json(['status' => 'fail', 'message' => $subValidate->getError(), 'data' => $subList]);
                }
            }

            Db::startTrans();
            try{
                // 创建奖项
                if(empty($id)){
                    // 创建抽奖
                    $data['create_time'] = date("Y-m-d H:i:s");
                    $ret = DrawModel::create($data)->toArray();
                }else{
                    // 编辑抽奖
                    $data['update_time'] = date("Y-m-d H:i:s");
                    $data['id'] = $id;
                    $ret = DrawModel::update($data)->toArray();
                }
                if(!$ret){
                    Db::rollback();
                    return json(['status'=>'fail' , 'message'=>'操作失败' , 'data'=>$ret]);
                }
                $id = $ret['id'];
                // 删除取消的奖项
                if(!empty($subRemoveList)) DrawSubModel::where('id' , 'in' , $subRemoveList)->delete();
                // 设置奖项详细信息
                foreach ($subList as $value){
                    if(!empty($value['id'])){
                        // 编辑
                        $DrawSubOne = DrawSubModel::where('id' , $value['id'])->findOrEmpty()->toArray();
                        if($DrawSubOne){
                            $ret = DrawSubModel::where('id' , $value['id'])->update([
                                'name' => $value['name'],
                                'address' => $value['address'],
                                'count' => $value['count'],
                                'over_count' => $DrawSubOne['over_count'] + ($value['count']-$DrawSubOne['count']),
                                'chance' => round($value['chance'] , 4),
                                'draw_id' => $id,
                                'update_time' => date("Y-m-d H:i:s")
                            ]);
                            if(!$ret){
                                Db::rollback();
                                return json(['status'=>'fail' , 'message'=>'编辑失败']);
                            }
                        }else{
                            Db::rollback();
                            return json(['status'=>'fail' , 'message'=>'编辑失败 ERROR 11023']);
                        }
                    }else{
                        // 创建
                        $ret = DrawSubModel::create([
                            'name' => $value['name'],
                            'address' => $value['address'],
                            'count' => $value['count'],
                            'over_count' => $value['count'],
                            'chance' => round($value['chance'] , 4),
                            'draw_id' => $id,
                            'create_time' => date("Y-m-d H:i:s")
                        ]);
                        if(!$ret){
                            Db::rollback();
                            return json(['status'=>'fail' , 'message'=>'创建失败']);
                        }
                    }
                }

                Db::commit();
                return json(['status'=>'ok' , 'message'=>'操作成功' , 'data'=>$ret]);

            }catch (\Exception $e){
                Db::rollback();
                return json(['status'=>'fail' , 'message'=>'操作失败' , 'data'=>$e->getMessage()]);
            }
        }

        return json(['status'=>'fail' , 'message'=>'未知的请求方式']);
    }

    // 删除抽奖
    public function removeDraw(){
        if(Request::isPost()){
            $ids = Request::param('ids' , 0);       // 批量删除
            $id = Request::param('id' , 0);         // 单个删除

            if(!empty($id)){
                $ret = DrawModel::where('id' , $id)->delete();
            }else if(!empty($ids) && is_array($ids)){
                $ret = DrawModel::where('id' , 'in' , $ids)->delete();
            }

            if(isset($ret) && $ret){
                return json(['status' => 'ok' , 'message' => '删除成功' , 'data' => $ret]);
            }

            return json(['status' => 'fail' , 'message' => '未找到需要删除的数据' , 'data' => ['id' => $id , 'ids' => $ids]]);
        }

        return json(['status' => 'fail' , 'message' => '未知的请求方式']);
    }

    // 中奖用户列表
    // 奖品使用情况列表
    public function getUserList(){
        if(Request::isPost()){

            $current = Request::param('current', 1);
            $pageSize = Request::param('pageSize', 20);
            $name = Request::param('name' , '');

            $where = [];
            $current = $current >= 1 ? $current : 1;
            $start = ($current - 1) * $pageSize;

            if(!empty($name)){
                $idList = \app\common\model\User::where([['nick_name|phone' ,'like' , "%{$name}%"]])->field('id')->select()->toArray();
                if(!empty($idList)) $ids = array_column($idList, 'id');
                if(empty($ids)){
                    return json(['status' => 'ok', 'message' => '获取成功', 'data'=>['list'=>[] , 'pagination' => ['current' => $current,'pageSize' => $pageSize,'total'=>0]]]);
                }
                $where[] = ['user_id' , 'in' , $ids];
            }

            $ret = WinUserModel::queryData($where , $start , $pageSize);
            $total = WinUserModel::total($where);

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

}