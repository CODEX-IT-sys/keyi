<?php

namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\PjTeacherStudent as PjTeacherStudentModel;
use think\Controller;
use think\Request;
use think\Db;

// 来稿需求 控制器
class PjTeacherStudent extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '',$pid = 0, $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_teacher_student');

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id', 3)->value('intro');

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field' => $colsData, 'colsData' => json_encode($colsData),
                'intro' => $intro, 'field' => $field, 'keyword' => $keyword
            ]);
        }

        // 调用模型获取列表
        $list = PjTeacherStudentModel::getList($search_type, $pid, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 学生列表
    public function student(Request $request, $search_type = '',$pid, $field = '', $keyword = '', $limit = 10)
    {

        // 用户id
        $uid = session('administrator')['id'];


        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['pid' => $pid]);
        }

        // 查询文件 列表
        $list = PjTeacherStudentModel::getList($search_type, $pid, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_inquiry');

        // 直接返回视图
        return view('', ['select_field' => $colsData]);
    }

    // 新增老师
    public function create()
    {

        // 直接返回视图
        return view('create', [

        ]);
    }

    // 新增学生
    public function add(Request $request)
    {
        $pid = $request->get('pid');

        // 直接返回视图
        return view('add', [
            'pid'=>$pid
        ]);
    }


    // 编辑视图
    public function edit($id)
    {
        $info = Db::name('pj_teacher_student')->where('delete_time',0)->where('id',$id)->find();
        // 直接返回视图
        return view('edit', [
            'info'=>$info
        ]);
    }



    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();


        // 更新时间
        $data['create_time'] = time();
        $data['pid'] = 0;

        // 保存
        PjTeacherStudentModel::create($data);

        // 返回操作结果
        return json(['msg' => '成功']);
    }

    // 新建 保存数据
    public function save_student(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();


        // 更新时间
        $data['create_time'] = time();


        // 保存
        PjTeacherStudentModel::create($data);

        // 返回操作结果
        return json(['msg' => '成功']);
    }



    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 更新时间
        $data['update_time'] = time();

        PjTeacherStudentModel::update($data);

        // 返回操作结果
        return json(['msg' => '修改成功']);

        // 返回操作结果
        //$this->redirect('index');
    }


    // 单条删除
    public function delete($id)
    {
        // 调用模型删除
        PjTeacherStudentModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 文件信息 单条删除
    public function file_delete($id)
    {
        // 调用模型删除
        MkInquiryFileModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 批量删除
    public function batch_delete(Request $request, $id)
    {
        // 栏目名
        $controller = $request->controller();

        $name = session('administrator')['name'];

        // 根据栏目 查询 读写权限
        $rw = Db::name('xt_rw_auth')
            ->where('name', $name)->where('C', $controller)
            ->value('delete');

        if (empty($rw)) {
            $this->error('无权操作');
        } else if ($rw == 0) {
            $this->error('无权操作');
        }

        $id_arr = explode(',', $id);

        // 调用模型删除
        foreach ($id_arr as $k => $v) {
            PjTeacherStudentModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 文件信息 批量删除
    public function file_batch_delete($id)
    {
        $id_arr = explode(',', $id);

        // 调用模型删除
        foreach ($id_arr as $k => $v) {
            MkInquiryFileModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }



    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 合同编码 获取相关信息
        $info = Db::name('mk_contract')
            ->field('Sales, Attention, Department, Company_Full_Name, Company_Name, VAT_Rate, Subject_Company,
             Subject_Company_VAT_ID, Subject_Company_Address, Subject_Company_Bank_Info')
            ->where('Contract_Number', $code)
            ->find();

        // 根据 合同编码 查询 客户联系人
        $customer = Db::name('mk_customer')
            ->field('Attention, Department')
            ->where('Contract_Number', $code)
            ->where('delete_time', 0)
            ->select();

        // 返回值
        return json(['data' => $info, 'c' => $customer]);
    }

    // 根据 客户联系人 查询 所属部门
    public function get_bm($customer)
    {
        // 根据 合同编码 获取相关信息
        $bm = Db::name('mk_customer')
            ->where('Attention', $customer)
            ->where('delete_time', 0)
            ->value('Department');

        // 返回值
        return json(['bm' => $bm]);
    }

    public function editing(Request $request)
    {


        try {
            $data = $request->param();
            $res = Db::name('mk_inquiry')->where('id', $data['id'])->update([$data['field'] => $data['value']]);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json($e->getMessage());
        }

        return json(['code' => $res]);
    }

    public function test(){
        $name = '曹紫云';
        //判断是否是老师
        $t = Db::name('pj_teacher_student')
            ->where('delete_time',0)
            ->where('pid',0)
            ->where('name',$name)
            ->find();
        if($t) {
            $stu = Db::name('pj_teacher_student')
                ->where('delete_time', 0)
                ->where('pid', $t['id'])
                ->select();

            $id_arr = [];
            foreach($stu as $k3 => $v3){
                $uname = $v3['name'];
                $pn = Db::name('pj_project_profile')
                    ->where('Translator', 'like', "%$uname%")
                    ->whereOr('Reviser', 'like', "%$uname%")
                    ->whereOr('Pre_Formatter', 'like', "%$uname%")
                    ->whereOr('Post_Formatter', 'like', "%$uname%")
                    ->field('id')
                    ->select();
                $arr2 = array_column($pn,'id');
                $id_arr = array_merge($id_arr,$arr2);
            }

            $teacher = Db::name('pj_project_profile')
                ->where('Translator', 'like', "%$name%")
                ->whereOr('Reviser', 'like', "%$name%")
                ->whereOr('Pre_Formatter', 'like', "%$name%")
                ->whereOr('Post_Formatter', 'like', "%$name%")
                ->field('id')
                ->select();
            $teacher = array_column($teacher,'id');
            $id_arr = array_merge($id_arr,$teacher);
            $id_arr = array_unique($id_arr);
        }
        /* $ids = [];
         foreach($id_arr as $k4 => $v4){
             $ids[] = $v4;
         }*/



        var_dump($id_arr);
    }


}