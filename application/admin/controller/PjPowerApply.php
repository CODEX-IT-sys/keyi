<?php

namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjPowerApply as PjPowerApplyModel;
use think\Controller;
use think\Request;
use think\Db;

// 权限申请 控制器
class PjPowerApply extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_power_apply');

        foreach ($colsData as $k => $v) {
            switch ($v['Field']) {
                case 'Filing_Code':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['fixed'] = 'left';
                    $colsData[$k]['sort'] = 'true';
                    break;

                case 'Filled_by':
                    $colsData[$k]['hide'] = 'true';
                    break;
                default:
                    $colsData[$k]['width'] = 110;

            }

        }

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id', 6)->value('intro');


        if ($request->has('search_type')) {
            $data = $request->only(['search_type']);
            $search_type = $data ["search_type"];
        }

        $job_id = session('administrator')['job_id'];
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field' => $colsData, 'colsData' => json_encode($colsData),
                'intro' => $intro, 'field' => $field, 'keyword' => $keyword,
                'search_type' => $search_type,'job_id'=>$job_id
            ]);
        }

        // 调用模型获取列表
        $list = PjPowerApplyModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_power_apply');

        // 直接返回视图
        return view('', ['select_field' => $colsData]);
    }

    // 显示新建的表单页
    public function create()
    {

        // 直接返回视图
        return view('form-apply', [

        ]);
    }


    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjPowerApplyModel::get($id);

        if($res['Approval_Result'] != 0){
            return $this->error('已审批，无法修改','index','','1');
        }
        return view('form-apply-view', [
            'info' => $res
        ]);
    }

    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();
        $name = session('administrator')['name'];
        $data['Filled_by'] = $name;
        // 保存
        PjPowerApplyModel::create($data);


        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();



        PjPowerApplyModel::update($data);

        echo "<script>history.go(-2);</script>";

    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjPowerApplyModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }




    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 合同编码 获取相关信息
        $info = Db::name('mk_feseability')
            ->where('Filing_Code', $code)->find();
        $info['Company_Full_Name'] = Db::table('ky_mk_invoicing')->where('Filing_Code', $code)->value('Company_Full_Name');;
        $fw_arr = explode(',', $info['Service']);

        // 服务类型
        $service = dict(5, $fw_arr);

        //页数
        //判断文件类型是否为Excel,是的话需要根据源语数量换算页数
        if($info['File_Type'] == 'Excel'){
            $info['Pages'] = round($info['Source_Text_Word_Count']/400);
        }

        // 返回值
        return json(['data' => $info, 'fw' => json_encode($service)]);
    }

    //审核批准  审核状态：0 待审核，1 批准，2 拒绝
    public function agree(Request $request){
        $id = $request->get('id');
        $name = session('administrator')['name'];
        $job_id = session('administrator')['job_id'];

        if(!in_array($job_id,[1,6,8,9])){
            return $this->error('没有权限操作','index','','1');
        }

        $status = Db::name('pj_power_apply')->where('id',$id)->value('Approval_Result');
        if($status != 0){
            return $this->error('已经审批过了，不能再次修改','index','','1');
        }

        $update = [
            'Approval_Result' => 1,
            'Approval_Date' => time(),
            'Approved_By' => $name,
        ];

        $res = Db::name('pj_power_apply')->where('id',$id)->update($update);

        // 返回操作结果
        $this->redirect('index');
    }

    //审核拒绝
    public function refuse(Request $request){
        $id = $request->get('id');

        $name = session('administrator')['name'];
        $job_id = session('administrator')['job_id'];
        if(!in_array($job_id,[1,6,8,9])){
            return $this->error('没有权限操作','index','','1');
        }

        $status = Db::name('pj_power_apply')->where('id',$id)->value('Approval_Result');
        if($status != 0){
            return $this->error('已经审批过了，不能再次修改','index','','1');
        }

        $update = [
            'Approval_Result' => 2,
            'Approval_Date' => time(),
            'Approved_By' => $name,
        ];

        $res = Db::name('pj_power_apply')->where('id',$id)->update($update);

        // 返回操作结果
        $this->redirect('index');
    }

    // 批量批准
    public function batch_pm($id)
    {
        $id_arr = explode(',', $id);

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id', $uid)->value('job_id');

        // 检查身份信息是否匹配
        if (!in_array($job_id,[1,6,8,9])) {

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        } else {  // 改变数据状态

            foreach ($id_arr as $k => $v) {

                Db::name('pj_power_apply')->where('id', $v)
                    ->update(['Approval_Result' => '1']);
            }

            // 返回数据
            return json(['msg' => '操作成功']);
        }
    }

}