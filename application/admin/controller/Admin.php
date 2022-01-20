<?php
namespace app\admin\controller;

use app\facade\Admin as AdminModel;
use think\Controller;
use think\Request;
use think\Db;

// 用户 账号管理
class Admin extends Controller
{
    // 用户的验证器名称
    private $validate = '\app\common\validate\Admin';

    // 验证失败抛出异常
    protected $failException = true;

    // 针对控制器方法的中间件
    protected $middleware = [
        'AjaxCheck' => ['only' => ['save', 'update', 'updatePassword']],
    ];

    /**
     * 显示用户列表
     * @param Request $request
     * @param string  $keyword 搜索关键词
     * @return Json|View
     * @throws \think\exception\DbException
     */
    public function index(Request $request, $field = '', $keyword = '', $limit = 10)
    {

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('list',['field'=>$field, 'keyword'=>$keyword]);
        }

        // 调用模型获取用户列表
        $list = AdminModel::getList($field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }


    //批量更新提前交付天数
    public function jsTime(){
        $data = Db::name('pj_contract_review')->where('delete_time',0)->field(['id','Delivery_Date_Expected','Completed','Early_days'])->select();
        foreach($data as $key=>$val){
            $expect = strtotime($val["Delivery_Date_Expected"]); //客户期望日期
            $expect = strtotime(date('Ymd',$expect));

            $completed = strtotime($val["Completed"]);
            $early_days = round(($completed - $expect)/86400);
            if($early_days >100 || $early_days < -100){
                $early_days = -999;
            }
            $where = [
                'delete_time'=>0,
                'id' => $val['id']
            ];
            $updata = [
                'Early_days' => $early_days
            ];
            $res = Db::table('ky_pj_contract_review')->where($where)->update($updata);
        }
    }
    //批量修改项目数据库Date
    public function gxDate(){
        $data = Db::name('pj_project_database')->where('delete_time',0)->field(['id','Filing_Code'])->select();
        foreach($data as $key=>$val){

            $date = substr($val['Filing_Code'], 2, 8);
            $where = [
                'delete_time'=>0,
                'id' => $val['id']
            ];
            $updata = [
                'Date' => $date
            ];
            $res = Db::table('ky_pj_project_database')->where($where)->update($updata);
        }
    }
    public function test(){
        $list = Db::name('mk_invoicing')->field(['id','Date_of_Balance','payment_time'])->limit(0,100)->select();
       /* foreach($list as $key=>$val){
             if(!empty($val['payment_time'])){
                 $data = [
                     'Date_of_Balance' => $val['payment_time']
                 ];
                  $res = Db::name('mk_invoicing')->where('id',$val['id'])->update($data);
             }
         }*/
        var_dump($list);
    }
    // 显示新建用户的表单页
    public function create()
    {
        // 查询部门信息
        $bm = Db::name('xt_department')->select();

        // 查询职位信息
        $job = Db::name('xt_job')->select();

        // 本职语种
        $yy = Db::name('xt_dict')->field('id as value, en_name as name')->where('c_id', 1)->select();

        // 直接返回视图
        return view('', ['bm'=>$bm, 'job'=>$job, 'yy'=>json_encode($yy)]);
    }

    // 保存新建的用户
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 数据验证
        $this->validate($data, 'app\common\validate\Admin');

        // 调用模型创建系统用户
        AdminModel::createAdmin($data);

        // 返回数据
        return json(['msg' => '创建成功']);
    }

    /**
     * 显示查看用户的表单页（不可编辑）
     * @param int $id
     * @return View
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        // 要查询的字段
        $field = ['id', 'name', 'email', 'phone', 'First_language', 'department_id', 'job_id', 'entry_time', 'dimission_time', 'sign', 'trainee', 'status'];

        // 调用模型获取用户信息
        $info = AdminModel::getOne($id, $field);

        $yy_arr = explode(',', $info['First_language']);

        // 语种
        $yy = Db::name('xt_dict')->field('id as value, en_name as name')->where('c_id', 1)->select();

        foreach ($yy as $k => $v){
            if(in_array($v['name'],$yy_arr)){
                $yy[$k]['selected'] = true;
            }
        }

        // 返回视图
        return view('', ['info'=>$info, 'yy'=>json_encode($yy)]);
    }

    /**
     * 显示编辑用户表单页
     * @param int $id
     * @return View
     * @throws \think\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
        // 要查询的字段
        $field = ['id', 'name', 'email', 'phone', 'First_language', 'department_id', 'job_id', 'entry_time', 'dimission_time', 'sign', 'trainee', 'status'];

        // 调用模型获取用户信息
        $info = AdminModel::getOne($id, $field);

        // 查询部门信息
        $bm = Db::name('xt_department')->select();

        // 查询职位信息
        $job = Db::name('xt_job')->select();

        // 语种
        $yy_arr = explode(',', $info['First_language']);

        $yy = Db::name('xt_dict')->field('id as value, en_name as name')->where('c_id', 1)->select();

        foreach ($yy as $k => $v){
            if(in_array($v['name'],$yy_arr)){
                $yy[$k]['selected'] = true;
            }
        }

        // 返回视图
        return view('', ['info' => $info, 'bm'=>$bm, 'job'=>$job, 'yy'=>json_encode($yy)]);
    }

    // 保存更新的用户
    public function update(Request $request,$id)
    {
        // 获取提交的数据
        $data = $request->put();

        // 数据验证（使用场景）
        $this->validate(array_merge($data, ['id' => $id]), $this->validate . '.' . $request->action());

        // 调用模型更新用户
        AdminModel::updateAdmin($data, $id);

        // 返回数据
        return json(['msg' => '更新成功']);
    }

    // 删除指定的用户
    public function delete($id)
    {
        // 调用模型删除用户
        AdminModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    /**
     * 显示修改密码的表单页
     * @param int $id
     * @return View
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editPassword($id)
    {
        // 调用模型，获取用户的名称
        $name = AdminModel::getFieldById($id, 'name');

        // 返回视图
        return view('', ['name' => $name, 'id'=>$id]);
    }

    // 更新密码
    public function updatePassword(Request $request,$id)
    {
        // 获取提交的数据
        $data = $request->put();

        // 数据验证（使用场景）
        $this->validate(array_merge($data, ['id' => $id]), $this->validate . '.' . $request->action());

        // 调用模型，更新密码
        AdminModel::updatePassword($id, $data['password']);

        // 返回数据
        return json(['msg' => '密码更新成功']);
    }

    // 签名（图片水印）上传
    public function up_sign()
    {
        // 文件对象
        $file = request()->file('file');

        // 获取文件后缀
        $temp = explode(".", $_FILES["file"]["name"]);
        $ext = end($temp);

        // 类型判断
        if(!in_array($ext,array("gif","jpeg","jpg","png"))){

            return json(['code' => 1, 'msg' => '上传失败，文件类型不合法']);
        }

        if ($file) {
            $info = $file->move('uploads', date('Ymd') . '/' .$_FILES["file"]["name"]);

            if ($info) {
                return json(['code' => 0, 'data' => '/uploads/' . $info->getSaveName(), 'msg' => '上传成功']);
            } else {
                return json(['code' => 1, 'data' =>'', 'msg' => $file->getError()]);
            }
        } else {
            return json(['code' => 1, 'data' =>'', 'msg' => '上传失败，请稍后再试']);
        }
    }
}