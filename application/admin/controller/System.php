<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/7/19
 * Time: 18:54
 */

namespace app\admin\controller;


use app\admin\validate\AdminValidate;
use app\common\controller\AdminBase;
use think\facade\Request;
use app\common\model\Admin as AdminModel;

class System extends AdminBase
{
    // 账号管理 - 列表
    public function getAdminList()
    {
        if (Request::isPost()) {
            $current = Request::param('current', 1);
            $pageSize = Request::param('pageSize', 20);

            $where = [];
            $current = $current >= 1 ? $current : 1;
            $start = ($current - 1) * $pageSize;

            $ret = AdminModel::queryData($where, $start, $pageSize);
            $total = AdminModel::total($where);

            return json(['status' => 'ok', 'message' => '获取成功', 'data' => [
                'list' => $ret,
                'pagination' => [
                    'current' => $current,
                    'pageSize' => $pageSize,
                    'total' => $total
                ]
            ]]);
        }

        return json(['status' => 'fail', 'message' => '未知的请求方式']);
    }

    // 账号管理 - 创建
    public function createAdmin()
    {
        if (Request::isPost()) {
            $id = Request::param('id', 0);
            $data['account'] = Request::param('account', '');
            $password = Request::param('password' , '');
            $data['roles'] = Request::param('roles', '');
            $data['roles'] = !empty($data['roles']) ? json_encode($data['roles']) : '';
            $data['type'] = Request::param('type' , '');

            $validate = new AdminValidate();
            if (!$validate->sceneCreateAccount()->check($data)) {
                return json(['status' => 'fail', 'message' => $validate->getError()]);
            }
            if (AdminModel::checkUserName($data['account'] , $id)) {
                return json(['status' => 'fail', 'message' => '该账号已存在']);
            }
            try {
                if ($id) {
                    // 编辑
                    unset($data['type']);
                    $data['id'] = $id;
                    empty($password)?:$data['password'] = encryptPass($password);
                    $data['update_time'] = date("Y-m-d H:i:s");
                    $ret = AdminModel::update($data)->toArray();
                } else {
                    // 创建
                    $data['create_time'] = date("Y-m-d H:i:s");
                    $data['role_type'] = 2;
                    $data['password'] = empty($password) ?encryptPass():encryptPass($password);
                    $ret = AdminModel::create($data)->toArray();
                }
            } catch (\Exception $e) {
                return json(['status' => 'fail', 'message' => $e->getMessage()]);
            }

            if (!$ret) {
                return json(['status' => 'fail', 'message' => '保存失败', 'data' => $ret]);
            }

            return json(['status' => 'ok', 'message' => '保存成功', 'data' => $ret]);
        }

        return json(['status' => 'fail', 'message' => '未知的请求方式']);
    }

    // 账号管理 - 删除
    public function removeAdmin()
    {
        if (Request::isPost()) {
            // 删除数据
            $id = Request::param('id', 0);
            if ($id) {
                $ret = AdminModel::where([['id', '=', $id], ['role_type', '=', 2]])->delete();

                if ($ret) {
                    return json(['status' => 'ok', 'message' => '删除成功', 'ret' => json_encode($ret)]);
                } else {
                    return json(['status' => 'fail', 'message' => "删除失败"]);
                }
            }
        }

        return json(['status' => 'fail', 'message' => '未知的请求方式']);
    }

    // 动态菜单
    public function getMenuData()
    {

        // 获取当前用户类型权限类型
        $user = $this->user;
        $role_type = isset($user['role_type']) ? $user['role_type'] : 0;
        $roles = !empty($user['roles']) ? json_decode($user['roles']) : [];
        $menu = AdminModel::getMenu($role_type , $roles);

        return json($menu);
    }

}