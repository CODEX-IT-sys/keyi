<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjPeople as PjPeopleModel;
use think\Controller;
use think\Request;
use think\Db;

class PjPeople extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    public function index(Request $request , $search_type = '', $field = '', $keyword = '', $limit = 50){
        // 数据库表字段集
        $colsData = getAllField('ky_pj_people');


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

        $list=  Db::table('ky_pj_people')->where('submit_date','>=',$firstTime)->where('delete_time',0)->where($where)->order('submit_date','asc')->select();
        //var_dump($list);die;

        $preDate = '';
        $totalRow = [];

        //合计数据
        $total_day = 0;
        $tpre = 0;
        $ttrs = 0;
        $trev = 0;
        $tpos = 0;
        $submit_page = 0;

        $pre_total = 0;
        $pre_page = [];
        $trs_total = 0;
        $trs_page = [];
        $rev_total = 0;
        $rev_page = [];
        $post_total	 = 0;
        $post_page = [];



        foreach($list as $key=>$val){

            //计算完成后 将新的循环日期赋值给临时变量


            $work_days = $val['work_days'];


            $pre_formatter = number_format($val['submit_page']/($val['pre_page']*$work_days),2);
            $list[$key]['pre_formatter']= $pre_formatter;
            $translator =number_format( $val['submit_page']/($val['trs_page']*$work_days),2);
            $list[$key]['translator']= $translator;
            $revisor = number_format($val['submit_page']/($val['rev_page']*$work_days),2);
            $list[$key]['revisor']= $revisor;
            $post_formatter = number_format($val['submit_page']/($val['post_page']*$work_days),2);
            $list[$key]['post_formatter']= $post_formatter;


            //合计行数据
            //$pre_total += $val['pre_total'];
            $pre_page[] = $val['pre_page'];
            //$trs_total += $val['trs_total'];
            $trs_page[] = $val['trs_page'];
           // $rev_total += $val['rev_total'];
            $rev_page[] = $val['rev_page'];
           // $post_total += $val['post_total'];
            $post_page[] = $val['post_page'];


            $total_day += $work_days;
            $tpre += $pre_formatter;
            $ttrs += $translator;
            $trev += $revisor;
            $tpos += $post_formatter;
            $submit_page += $val['submit_page'];



        }
        //$totalRow['pre_total'] = $pre_total;
        if(!empty(array_sum($pre_page))){
            $totalRow['pre_page'] = number_format(array_sum($pre_page)/count($pre_page),2);
        }

        //$totalRow['trs_total'] = $trs_total;
        if(!empty(array_sum($trs_page))) {
            $totalRow['trs_page'] = number_format(array_sum($trs_page) / count($trs_page), 2);
        }
        if(!empty(array_sum($rev_page))) {
            $totalRow['rev_page'] = number_format(array_sum($rev_page) / count($rev_page), 2);
        }
        if(!empty(array_sum($post_page))) {
            //$totalRow['post_total'] = $post_total;
            $totalRow['post_page'] = number_format(array_sum($post_page) / count($post_page), 2);
        }

        $totalRow['work_days'] = $total_day;
        $totalRow['pre_formatter'] = $tpre;
        $totalRow['translator'] = $ttrs;
        $totalRow['revisor'] = $trev;
        $totalRow['post_formatter'] = $tpos;
        $totalRow['submit_page'] = $submit_page;


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
        //默认人数
        $info = [
            'pre' => 70,
            'trs' => 20,
            'rev' => 25,
            'pos' => 150
        ];

        // 直接返回视图
        return view('form-pj_people', ['info'=>$info]);
    }

    public function check(Request $request){
        try {
            $data=$request->param();
            //提交日期
            $nowDate = $data['sub_date'];
            //填写日期
            $day = $data['day'];

            $preDate = date('Ymd');
            $days = round((strtotime($nowDate)-strtotime($preDate))/3600/24);
            //$list[$key]['work_days'] = $days;
            $preN = date('N', strtotime($preDate));
            $nowN = date('N', strtotime($nowDate));


            $weekday = $this->get_weekend_days($preDate,$nowDate);
            $reduce = 0;
            if($preN == 6 || $preN == 7){
                $reduce += 1;
            }
            if($nowN == 6 || $nowN == 7){
                $reduce += 1;
            }

            $cz = $days-$weekday+$reduce;
            if($cz != $day){
                return json(['code'=>6666,]);
            }
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json(['code'=>9999,'error'=>$e->getMessage()]);
        }

        return json(['code'=>1]);
    }
    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        PjPeopleModel::create($data);

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
        $res = PjPeopleModel::get($id);
        return view('form-pj_people-view', [
            'info'=>$res,
        ]);
    }

    public function update(Request $request){
        $data = $request->post();
        if(empty($data)){
            return $this->error('数据不能为空');
        }

        $res = Db::name('pj_people')
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
        PjPeopleModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    //计算两个日期之间周末的天数
    function get_weekend_days($start_date, $end_date, $weekend_days=2) {

        $data = array();
        if (strtotime($start_date) > strtotime($end_date)) list($start_date, $end_date) = array($end_date, $start_date);

        $start_reduce = $end_add = 0;
        $start_N      = date('N',strtotime($start_date));
        $start_reduce = ($start_N == 7) ? 1 : 0;

        $end_N = date('N',strtotime($end_date));

        // 进行单、双休判断，默认按双休计算
        $weekend_days = intval($weekend_days);
        switch ($weekend_days)
        {
            case 2:
                in_array($end_N,array(6,7)) && $end_add = ($end_N == 7) ? 2 : 1;
                break;
            case 1:
            default:
                $end_add = ($end_N == 7) ? 1 : 0;
                break;
        }

        $days = round(abs(strtotime($end_date) - strtotime($start_date))/86400) + 1;
        $data['total_days'] = $days;
        $data['total_relax'] = floor(($days + $start_N - 1 - $end_N) / 7) * $weekend_days - $start_reduce + $end_add;

        return $data['total_relax'];
    }



}