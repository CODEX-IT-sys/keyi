<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjContractReview as PjContractReviewModel;
use app\facade\PjTask as PjTaskModel;
use think\Controller;
use think\Request;
use think\Db;

class PjTask extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    public function index(Request $request , $search_type = '', $field = '', $keyword = '', $limit = 50){
        // 数据库表字段集
        $colsData = getAllField('ky_pj_task');


        $name = session('administrator')['name'];
        $job_id = session('administrator')['job_id'];
        // 查询器对象 判断管理层
        if(in_array($job_id, [1,8,9,20])) {

           $where = '';

            // 翻、校、排、助理
        }else{
            $where = [
                'Filled_by' => $name
            ];
        }

        //第一行从当前时间开始
            $time= time();
            $firstTime = strtotime(date("Y-m-d"),time());

            $firstTime=intval(date("Ymd",$firstTime));

        $list=  Db::table('ky_pj_task')->where('submit_date','>=',$firstTime)->where('delete_time',0)->where($where)->order('submit_date','asc')->select();
        //var_dump($list);

        $preDate = '';
        $totalRow = [];

        //合计数据
        $total_day = 0;
        $tpre = 0;
        $ttrs = 0;
        $trev = 0;
        $tpos = 0;
        $submit_page = 0;

        $pre_formatter = 0;
        $pre_page = [];
        $translator = 0;
        $trs_page = [];
        $revisor = 0;
        $rev_page = [];
        $post_formatter = 0;
        $post_page = [];
        //平均值
        $tr_gpa = [];
        $tr_gpe = [];
        $pre_gpa = [];
        $pre_gpe = [];
        $post_gpa = [];
        $post_gpe = [];


        foreach($list as $key=>$val){

            //计算完成后 将新的循环日期赋值给临时变量


            $work_days = $val['work_days'];


            $pre_total = $val['pre_formatter']*$val['pre_page']*$work_days;
            $list[$key]['pre_total']= $pre_total;
            $trs_total = $val['translator']*$val['trs_page']*$work_days;
            $list[$key]['trs_total']= $trs_total;
            $rev_total = $val['revisor']*$val['rev_page']*$work_days;
            $list[$key]['rev_total']= $rev_total;
            $post_total = $val['post_formatter']*$val['post_page']*$work_days;
            $list[$key]['post_total']= $post_total;

            $list[$key]['trre_GapPages'] = $val['submit_page']-$list[$key]['trs_total']-$list[$key]['rev_total'];
            $list[$key]['trre_GapPeople'] = number_format($list[$key]['trre_GapPages']/25, 2);
            $list[$key]['pre_GapPages'] = $val['submit_page']-$list[$key]['pre_total'];
            $list[$key]['pre_GapPeople'] = number_format($list[$key]['pre_GapPages']/$val['pre_page'],2);;
            $list[$key]['post_GapPages'] = $val['submit_page']-$list[$key]['post_total'];
            $list[$key]['post_GapPeople'] = number_format($list[$key]['post_GapPages']/$val['post_page'],2);

            //合计行数据
            $pre_formatter += $val['pre_formatter'];
            $pre_page[] = $val['pre_page'];
            $translator += $val['translator'];
            $trs_page[] = $val['trs_page'];
            $revisor += $val['revisor'];
            $rev_page[] = $val['rev_page'];
            $post_formatter += $val['post_formatter'];
            $post_page[] = $val['post_page'];

            $total_day += $work_days;
            $tpre += $pre_total;
            $ttrs += $trs_total;
            $trev += $rev_total;
            $tpos += $post_total;
            $submit_page += $val['submit_page'];

            $tr_gpa[] = $list[$key]['trre_GapPages'];
            $tr_gpe[] = $list[$key]['trre_GapPeople'];
            $pre_gpa[] = $list[$key]['pre_GapPages'];
            $pre_gpe[] = $list[$key]['pre_GapPeople'];
            $post_gpa[] = $list[$key]['post_GapPages'];
            $post_gpe[] = $list[$key]['post_GapPeople'];



        }
        $totalRow['pre_formatter'] = $pre_formatter;
        if(!empty(array_sum($pre_page))) {
            $totalRow['pre_page'] = number_format(array_sum($pre_page) / count($pre_page), 2);
        }
        $totalRow['translator'] = $translator;
        if(!empty(array_sum($trs_page))) {
            $totalRow['trs_page'] = number_format(array_sum($trs_page) / count($trs_page), 2);
        }
        $totalRow['revisor'] = $revisor;
        if(!empty(array_sum($rev_page))) {
            $totalRow['rev_page'] = number_format(array_sum($rev_page) / count($rev_page), 2);
        }
        $totalRow['post_formatter'] = $post_formatter;
        if(!empty(array_sum($post_page))) {
            $totalRow['post_page'] = number_format(array_sum($post_page) / count($post_page), 2);
        }

        $totalRow['work_days'] = $total_day;
        $totalRow['pre_total'] = $tpre;
        $totalRow['trs_total'] = $ttrs;
        $totalRow['rev_total'] = $trev;
        $totalRow['post_total'] = $tpos;
        $totalRow['submit_page'] = $submit_page;
        if(!empty(array_sum($tr_gpa))) {
            $totalRow['trre_GapPages'] = number_format(array_sum($tr_gpa) / count($tr_gpa), 2);
        }
        if(!empty(array_sum($tr_gpe))) {
            $totalRow['trre_GapPeople'] = number_format(array_sum($tr_gpe) / count($tr_gpe), 2);
        }
        if(!empty(array_sum($pre_gpa))) {
            $totalRow['pre_GapPages'] = number_format(array_sum($pre_gpa)/count($pre_gpa),2);
        }
        if(!empty(array_sum($pre_gpe))) {
            $totalRow['pre_GapPeople'] = number_format(array_sum($pre_gpe) / count($pre_gpe), 2);
        }
        if(!empty(array_sum($post_gpa))) {
            $totalRow['post_GapPages'] = number_format(array_sum($post_gpa) / count($post_gpa), 2);
        }
        if(!empty(array_sum($post_gpe))) {
            $totalRow['post_GapPeople'] = number_format(array_sum($post_gpe) / count($post_gpe), 2);
        }

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {

               $this->assign(['list'=>$list,'colsData'=>$colsData]);

               return $this->fetch();



        }
        return [
            'code'  => 0,
            'msg'   => '',
            'count' => 0,
            'data'  =>$list,
            'totalRow' => $totalRow,
        ];

        // 返回数据
        //return json(generate_layui_table_data($list));
    }

    public function create()
    {
        // 直接返回视图
        return view('form-pj_task', []);
    }


    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        PjTaskModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }


    public function test(){
        $a = $this->get_weekend_days('20211021','20211023');
        var_dump($a);
    }


    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjTaskModel::get($id);
        return view('form-pj_task-view', [
            'info'=>$res,
        ]);
    }

    public function update(Request $request){
        $data = $request->post();
        if(empty($data)){
            return $this->error('数据不能为空');
        }

        $res = Db::name('pj_task')
            ->where('id',$data['id'])
            ->update($data);

        if(!$res){
            return $this->error('修改失败');
        }
            // 返回操作结果
            $this->redirect('index');

    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjTaskModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }




}