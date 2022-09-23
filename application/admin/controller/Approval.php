<?php

namespace app\admin\controller;


//FAQ管理
use app\common\model\Admin;
use app\common\controller\Common;
use app\facade\PjApproval as ApprovalModel;
use think\Db;
use think\Request;
use think\Controller;

class Approval extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_approval');

        foreach ($colsData as $k => $v) {
            switch ($v['Field']) {
                case 'title':
                    $colsData[$k]['width'] = 300;
                    $colsData[$k]['fixed'] = 'left';
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'content':
                    $colsData[$k]['hide'] = 'true';
                    break;

                case 'Filled_by':
                    $colsData[$k]['hide'] = 'true';
                    break;

                default:
                    ;

            }

        }

        // 查询分类信息
        $intro = Db::name('xt_table_text')->where('id', 17)->value('intro');

        if ($request->has('search_type')) {
            $data = $request->only(['search_type']);
            $search_type = $data ["search_type"];
        }
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field' => $colsData, 'colsData' => json_encode($colsData),
                'intro' => $intro, 'field' => $field, 'keyword' => $keyword,
                'search_type' => $search_type
            ]);
        }


        // 调用模型获取列表
        $list = ApprovalModel::getList($search_type, $field, $keyword, $limit);


        // 返回数据
        return json(generate_layui_table_data($list));
    }

    public function apply(){
        $list = Db::name('mk_requirement')->where('delete_time',0)->field('Customer_Company,Attention')->select();
        $company = array_column($list,'Customer_Company');
        $company = array_unique($company);

        $attention = array_column($list,'Attention');
        $attention = array_unique($attention);

        return view('',['company'=>$company,'attention'=>$attention]);
    }

    public function add_apply(Request $request){
        $data = $request->post();

        $data['create_time'] = time();
        $data['belong'] = '客户要求';

        $res = Db::name('pj_approval')->insert($data);
        // 返回数据
        return json(['msg' => '申请成功']);
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_approval');

        // 直接返回视图
        return view('', ['select_field' => $colsData]);
    }



    // 删除
    public function delete($id)
    {
        // 查询信息
        $res = ApprovalModel::get($id);

        $job_id = session('administrator')['job_id'];
        $name = session('administrator')['name'];
        if (!in_array($job_id, [1, 8, 9, 20])) {
            if ($res['Filled_by'] != $name) {
                return $this->error('非本人不能删除');
            }
        }
        // 调用模型删除
        ApprovalModel::destroy($id);

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

        // 返回值
        return json(['data' => $info, 'fw' => json_encode($service)]);
    }

    public function upload(Request $request)
    {
        //设置php 不超时
        @ini_set('memory_limit', '-1');
        @set_time_limit(0);
        $param = $request->param();
        $file = $request->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move('uploads');
            if ($info) {
                $url = '/uploads/' . $info->getSaveName();
                $data['code'] = 0;
                $data['msg'] = '';
                $data['data']['src'] = $url;
                return json($data);
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }



    public function getExcel(Request $request, $search_type = '', $field = '', $keyword = ''){

        $cate = '电脑软件';

        // 调用模型获取列表
        $list = ApprovalModel::getList($search_type, $field, $keyword);

        return $list;
    }

    //返回选中的行的值
    public function getSelect(Request $request){
        // 获取提交的数据
        $data = $request->post();
        $id = $data['c_id'];
        $id_arr = explode(',', $id);
        $id_arr = array_reverse($id_arr);


        $res = Db::name('faq')->whereIn('id',$id_arr)
            ->select();
        $list = [
            'code'=> 200,
            'msg' => 'success',
            'data' => $res,
        ];
        return $list;
    }

    //审核批准  审核状态：0 待审核，1 批准，2 拒绝
    public function agree(Request $request){
        $id = $request->get('id');

        $update = [
            'status' => 1,
            'apr_time' => time(),
        ];

        $res = Db::name('pj_approval')->where('id',$id)->update($update);

        // 返回操作结果
        $this->redirect('index');
    }

    //审核拒绝
    public function refuse(Request $request){
        $id = $request->get('id');

        $update = [
            'status' => 2,
            'apr_time' => time(),
        ];

        $res = Db::name('pj_approval')->where('id',$id)->update($update);

        // 返回操作结果
        $this->redirect('index');
    }

    public function test(){
        //判断审批通过的
        $where = [
            'belong' => '客户要求',
            'name' => 'PA01',
            'status' => 1
        ];
        $approval = Db::name('pj_approval')->where($where)->select();

        $arr_id = [];
        foreach($approval as $key=>$val){
            $l = Db::name('mk_requirement')
                ->where('Customer_Company',$val['company'])
                ->where('Attention',$val['attention'])
                ->where('delete_time',0)
                ->field('id')
                ->select();
            $id = array_column($l,'id');
            $arr_id = array_merge($arr_id,$id);
        }
        $arr_id = array_values($arr_id);
        halt($arr_id);
    }

    // 项目经理 批量确认
    public function batch_pm($id)
    {
        $id_arr = explode(',', $id);

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id', $uid)->value('job_id');

        // 检查身份信息是否匹配
        if ($job_id != 1 && $job_id != 8) {

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        } else {  // 改变数据状态

            foreach ($id_arr as $k => $v) {

                Db::name('pj_approval')->where('id', $v)
                    ->update(['status' => '1']);
            }

            // 返回数据
            return json(['msg' => '操作成功']);
        }
    }

}