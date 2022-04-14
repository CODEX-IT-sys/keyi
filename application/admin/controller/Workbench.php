<?php
namespace app\admin\controller;

use app\facade\PjProjectProfile as PjProjectProfileModel;
use app\facade\Admin as AdminModel;
use think\Controller;
use think\Request;
use think\Db;

// 我的工作台 控制器
class Workbench extends Controller
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $type = 0, $limit = 10)
    {

        // 用户id 用户名
        $id = session('administrator')['id'];
        $name = session('administrator')['name'];
        // 查询用户职位
        $job_id = Db::name('admin')->where('id', $id)->value('job_id');

        // 今日
        $now = date('Ymd');  $data = [];

        // 时间戳
        $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        // 格式时间
        $beginToday = date('Y-m-d H:i',$beginToday);
        $endToday = date('Y-m-d H:i',$endToday);

        // 现在时刻
        $now_time = date('Y-m-d H:i');

        // 人员、时间查询条件
        $where_name = '';
        $where_time = '';

        // 预定义
        $submit = [];
        $pending = [];
        $library = [];
        $today = [];
        $exception = [];

        /*判断用户类型 显示对应数据*/

        // 市场部 (市场行政、市场助理、市场行政经理、财务)
        if(in_array($job_id,[2,14,20])) {

            // 市场行政经理
            if($job_id == 20){
                // 来稿确认 待批准的
                $pending = Db::name('mk_feseability')
                    ->where('Approval_Sales_Admin_Manager', '<>', 'Yes')
                    ->where('Approval_General_Manager', '<>', 'Yes')
                    ->where('delete_time',0)
                    ->order('id desc')
                    ->paginate($limit);

                // 待更库项目  项目数据库 是否已更库（否）
                $library = Db::name('pj_project_database')
                    ->field('id, Filing_Code, Job_Name')
                    ->where('Update_Company_TM', 'No')
                    ->where('Update_File_TM', 'No')
                    ->where('delete_time', 0)
                    ->order('id desc')
                    ->paginate($limit);

                // 进度异常
                $exception = Db::name('pj_contract_review')
                    ->field('id, Filing_Code, Job_Name')
                    /* ->where(function($query){
                         $query->where('Delivered_or_Not','No')->whereOr('Feedback_Completed','No');
                     })*/
                    ->where('Delivered_or_Not','No')
                    ->where('delete_time',0)
                    ->where('Completed', 'not in', ['', 0, NULL])
                    ->where('Completed', '<', $now)
                    ->order('id desc')
                    ->paginate($limit);



                // 管理层
                $job_id = 1;

            }else if($job_id == 2){

                // 待安排
                $pending = [];
                // 待更库
                $library = [];
                // 进度异常
                $exception = Db::name('pj_contract_review')
                    ->field('id, Filing_Code, Job_Name')
                    ->where('Delivered_or_Not','No')
                    ->where('delete_time',0)
                    ->where('Completed', 'not in', ['', 0, NULL])
                    ->where('Completed', '<', $now)
                    ->order('id desc')
                    ->paginate($limit);

                // 用于显示 控制
                $job_id = 1;

            }else{
                // 待安排
                $pending = [];
                // 待更库
                $library = [];
                // 进度异常
                $exception = [];

                // 非管理层 只显示项目列表
                $job_id = 0;
            }

            // 待提交(项目汇总表 是否交稿 为 否 的)
            $submit = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Delivered_or_Not', 'No')
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);

            // 今日提交 交付时间 为今天的
            $today = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Delivered_or_Not','No')
                ->where('delete_time',0)
                ->where('Completed', $now)
                ->order('id desc')
                ->paginate($limit);

            // 今日提交总页数 交付时间 为今天的
            $todayPages = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name,Pages')
                ->where('Delivered_or_Not','No')
                ->where('delete_time',0)
                ->where('Completed', $now)
                ->order('id desc')
                ->select();

            // 反馈修订是否提交
            $feedback = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Feedback_Completed', 'No')
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);

            // 项目部 (10翻译人员  11校对人员  12、13预、后排版人员)
        } else if(in_array($job_id, [4,5,6,10,11,12,13,15,19])){

            // 针对不同人员 查询字段不同
            if($job_id == 10 or $job_id == 19){
                $where_time = 'Translation_Delivery_Time';
                $where_name = 'Translator';
            }
            if(in_array($job_id, [4,6,11,15])){
                $where_time = 'Revision_Delivery_Time';
                $where_name = 'Reviser';
            }
            if($job_id == 12){
                $where_time = 'Pre_Format_Delivery_Time';
                $where_name = 'Pre_Formatter';
            }
            if($job_id == 13 or $job_id == 5){
                $where_time = 'Post_Format_Delivery_Time';
                $where_name = 'Post_Formatter';
            }

            // 待安排项目
            $pending = [];

            // 待更库项目
            $library = [];

            // 待提交
            $submit = Db::name('pj_project_profile')
                ->field('id, Filing_Code, Job_Name')
                ->where('delete_time',0)
                ->where($where_name, 'like', $name)
                ->where($where_time,'>',$endToday)
                ->order('id desc')
                ->paginate($limit);


            // 今日提交
            $today =  Db::name('pj_project_profile')
                ->field('id, Filing_Code, Job_Name')
                ->where('delete_time',0)
                ->where($where_name, 'like', $name)
                ->whereBetweenTime($where_time,$beginToday,$endToday)
                ->order('id desc')
                ->paginate($limit);



            // 今日提交总页数 交付时间 为今天的
            $todayPages = Db::name('pj_project_profile')
                ->field('id, Filing_Code, Job_Name,Pages')
                ->where('delete_time',0)
                ->where($where_name, 'like', $name)
                ->whereBetweenTime($where_time,$beginToday,$endToday)
                ->order('id desc')
                ->select();

            // 进度异常
            /*$exception =  Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Delivered_or_Not', 'No')
                ->where('Completed', 'not in', ['', 0, NULL])
                ->where('delete_time',0)
                ->where($where_name, 'like', $name)
                ->where($where_time,'<', $now_time)
                ->order('id desc')
                ->paginate($limit);*/

            $exception = [];


            // 将职位置0 表示非管理层
            $job_id = 0;

        } else if(in_array($job_id,[7,8,9])) {
            // 项目管理人员(7项目组长、8项目经理、9总经理)
            if($job_id == 7){
                $where_name = 'PA';
            }
            if($job_id == 8){
                $where_name = 'PM';
            }
            if($job_id == 9){
                $where_name = '';
            }

            // 待安排项目   项目助理为空的
            $pending = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Delivered_or_Not', 'No')
                ->where('PA', '')
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);


            // 待提交(项目汇总表 是否交稿 为 否 的)
            $submit = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Delivered_or_Not', 'No')
                ->where($where_name, $name)
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);

            $where1 = [
                'Delivered_or_Not' => 'No',
                'Feedback_Completed' => 'No'
            ];

            // 今日提交 交付时间 为今天的
            $today = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Delivered_or_Not','No')
                ->where($where_name, $name)
                ->where('delete_time',0)
                ->where('Completed', $now)
                ->order('id desc')
                ->paginate($limit);

            // 反馈修订是否提交
            $feedback = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Feedback_Completed', 'No')
                ->where($where_name, $name)
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);

            // 今日提交总页数 交付时间 为今天的
            $todayPages = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name,Pages')
                ->where('Delivered_or_Not','No')
                ->where($where_name, $name)
                ->where('delete_time',0)
                ->where('Completed', $now)
                ->order('id desc')
                ->select();

            // 进度异常
            $exception = Db::name('pj_contract_review')
                ->field('id, Filing_Code, Job_Name')
                ->where('Delivered_or_Not','No')
                ->where($where_name, $name)
                ->where('delete_time',0)
                ->where('Completed', 'not in', ['', 0, NULL])
                ->where('Completed','<', $now)
                ->order('id desc')
                ->paginate($limit);


            if($job_id == 8){
                $where_name = '';
            }

            // 待更库项目  项目数据库 是否已更库（否）
            $library = Db::name('pj_project_database')
                ->field('id, Filing_Code, Job_Name')
                ->where($where_name, $name)
                ->where('Update_Company_TM', 'No')
                ->where('Update_File_TM', 'No')
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);

            // 管理层 显示全部5项
            $job_id = 1;
        }

        // 列表数据 计数
        if(empty($submit)){
            $s_data = [
                'code'  => 0,
                'msg'   => '',
                'count' => 0,
                'data'  => [],
            ];
        } else {
            $s_data = generate_layui_table_data($submit);
        }
        $t['s'] = $s_data['count'];

        if(empty($pending)){
            $p_data = [
                'code'  => 0,
                'msg'   => '',
                'count' => 0,
                'data'  => [],
            ];
        } else {
            $p_data = generate_layui_table_data($pending);
        }
        $t['p'] = $p_data['count'];

        if(empty($library)){
            $l_data = [
                'code'  => 0,
                'msg'   => '',
                'count' => 0,
                'data'  => [],
            ];
        } else {
            $l_data = generate_layui_table_data($library);
        }
        $t['l'] = $l_data['count'];

        if(empty($today)){
            $t_data = [
                'code'  => 0,
                'msg'   => '',
                'count' => 0,
                'data'  => [],
            ];
        } else {
            $t_data = generate_layui_table_data($today);
        }
        $t['t'] = $t_data['count'];

        $totalPages = 0;
        if(empty($todayPages)){
            $tp_data = [
                'code'  => 0,
                'msg'   => '',
                'count' => 0,
                'data'  => [],
            ];
        } else {

            foreach($todayPages as $key=>$val){
                $page = $val['Pages'];
                $totalPages +=$page;
            }
            $tp_data = generate_layui_table_data($today);
        }
        $t['tp'] = $totalPages;

        if(empty($exception)){
            $e_data = [
                'code'  => 0,
                'msg'   => '',
                'count' => 0,
                'data'  => [],
            ];
        } else {
            $e_data = generate_layui_table_data($exception);
        }
        $t['e'] = $e_data['count'];

        if(empty($feedback)){
            $f_data = [
                'code'  => 0,
                'msg'   => '',
                'count' => 0,
                'data'  => [],
            ];
        } else {
            $f_data = generate_layui_table_data($feedback);
        }
        $t['f'] = $f_data['count'];


        // 判断 type 参数 赋值列表
        switch ($type) {
            case 0:
                $data = $s_data;
                break;

            case 1:
                $data = $p_data;
                break;

            case 2:
                $data = $s_data;
                break;

            case 3:
                $data = $l_data;
                break;

            case 4:
                $data = $t_data;
                break;

            case 5:
                $data = $e_data;
                break;
            case 6:
                $data = $f_data;
                break;
        }

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['job_id'=>$job_id, 't'=>$t, 'type'=>$type]);
        }

        // 返回数据
        return json($data);
    }

}