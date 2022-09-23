<?php
namespace app\admin\controller;

use app\facade\Admin as AdminModel;
use think\Controller;
use think\Request;
use think\Db;
use think\Env;

// 数据统计 项目通道 控制器
class ProjectFunnel extends Controller
{

    /*
    * 项目通道  （允许范围 当前年份 四周、12个月）
    * @param   string            $type           时间类型（M：月份 W：周）
    * @param   string            $time           时间值（如：202006、W1）
    * @throws \think\exception\Exception
    * */
    public function index(Request $request, $type = '', $time = '')
    {
        // 预定义数组
        $arr = [];

        $s = intval(date('Ym')  . '01');
        $d = intval(date('Ym')  . '31');

        /*
         * 时间范围 查询条件 判断
         * */
        if($type == ''){
            // 为空时 默认当前 年月
            $type = 'M';
            $time = date('m');

            $s = intval(date('Ym')  . '01');
            $d = intval(date('Ym')  . '31');

        }else{
            // 判断时间类型
            switch ($type) {
                case 'M':
                    $s = intval(date('Y') . $time . '01');
                    $d = intval(date('Y') . $time . '31');
                    break;

                case 'W':
                    // 判断周
                    switch ($time) {
                        case 'W1':
                            $s = intval(date('Ym') . '01');
                            $d = intval(date('Ym') . '07');
                            break;
                        case 'W2':
                            $s = intval(date('Ym') . '08');
                            $d = intval(date('Ym') . '14');
                            break;
                        case 'W3':
                            $s = intval(date('Ym') . '15');
                            $d = intval(date('Ym') . '21');
                            break;
                        case 'W4':
                            $s = intval(date('Ym') . '22');
                            $d = intval(date('Ym') . '31');
                            break;
                    }
                    break;
            }
        }


        /*
         * 前五列：语言条件 数据（页数、词数[源语数量]信息）
         * */
        $l_a = ['En-CN', 'CN-EN', '%-CN', 'CN-%'];

        // 分别查询数据
        $e_c = Db::name('mk_feseability')
            ->field('SUM(Pages) Pages, SUM(Source_Text_Word_Count) Word')
            ->where('Language','En-CN')
            ->where('delete_time',0)
            ->whereBetweenTime('Assigned_Date', $s, $d)
            ->find();

        $c_e = Db::name('mk_feseability')
            ->field('SUM(Pages) Pages, SUM(Source_Text_Word_Count) Word')
            ->where('Language','CN-EN')
            ->where('delete_time',0)
            ->whereBetweenTime('Assigned_Date', $s, $d)
            ->find();

        $o_c = Db::name('mk_feseability')
            ->field('SUM(Pages) Pages, SUM(Source_Text_Word_Count) Word')
            ->where('Language', 'like', '%-CN')
            ->where('Language','<>','EN-CN')
            ->where('delete_time',0)
            ->whereBetweenTime('Assigned_Date', $s, $d)
            ->find();

        $c_o = Db::name('mk_feseability')
            ->field('SUM(Pages) Pages, SUM(Source_Text_Word_Count) Word')
            ->where('Language', 'like', 'CN-%')
            ->where('Language','<>','CN-EN')
            ->where('delete_time',0)
            ->whereBetweenTime('Assigned_Date', $s, $d)
            ->find();


        /*
         * 文件编号 作为进度的查询条件 未交稿
         * */
        $f_n = Db::name('pj_contract_review')
            ->field('Filing_Code')
            ->where('Delivered_or_Not','No')
            ->where('delete_time',0)
            ->select();

        if(!empty($f_n)){
            foreach ($f_n as $k => $v){
                $file_no[] = $v['Filing_Code'];
            }
        }else{
            $file_no = [];
        }


        /*
         * 进度表 填数据的
         * */
        $jd_f_n = Db::name('pj_daily_progress_tr_re')
            ->where('Filing_Code', 'in', $file_no)
            ->field('Filing_Code')
            ->group('Filing_Code')
            ->select();


        $jd_f = [];
        if(!empty($jd_f_n)){
            foreach ($jd_f_n as $k => $v){
                $jd_f[] = $v['Filing_Code'];
            }
        }


        // 进度表 没填的 取差集
        $k_f = array_diff($file_no,$jd_f);


        /*2020-11-23 确认 后半部分 不限时间范围 所有数据*/

        // 格式时间转 时间戳
        //$s = strtotime($s);
        //$d = strtotime($d);

        // 格式化时间
        //$s = date('Y-m-d H:i',$s);
        //$d = date('Y-m-d H:i',$d);

        /*
         * 进度信息（翻译每日进度）
         * */
        // 已完成 50%以上 总页数
        $jd_a_f = Db::name('pj_daily_progress_tr_re')
            ->field('Work_Date,Filing_Code,SUM(Number_of_Pages_Completed) pages')
            ->where('Filing_Code', 'in', $file_no)
            ->where('Category', 'TR')
            ->where('Work_Content', 'Translate')
            ->where('delete_time',0)
            //->whereBetweenTime('Work_Date', $s, $d)
            ->where('Percentage_Completed', '>', 50)
            //->order('Work_Date desc')
            ->group('Work_Date,Filing_Code')
            ->select();


        $jd_a['pages'] = 0;
        if(!empty($jd_a_f)){
            foreach ($jd_a_f as $k => $v){
                $jd_a['pages'] += $v['pages'];
            }
        }


        // 已完成 10-50% 总页数
        $jd_b_f = Db::name('pj_daily_progress_tr_re')
            ->field('Work_Date,Filing_Code,SUM(Number_of_Pages_Completed) pages')
            ->where('Filing_Code', 'in', $file_no)
            ->where('Category', 'TR')
            ->where('Work_Content', 'Translate')
            ->where('delete_time',0)
            //->whereBetweenTime('Work_Date', $s, $d)
            ->whereBetween('Percentage_Completed', [10,50])
            //->order('Work_Date desc')
            ->group('Work_Date,Filing_Code')
            ->select();

        $jd_b['pages'] = 0;
        if(!empty($jd_b_f)){
            foreach ($jd_b_f as $k => $v){
                $jd_b['pages'] += $v['pages'];
            }
        }


        // 未开始 页数 未提交 进度10%以下的
        $jd_c_f = Db::name('pj_daily_progress_tr_re')
            ->field('Work_Date,Filing_Code,SUM(Number_of_Pages_Completed) pages')
            ->where('Filing_Code', 'in', $jd_f)
            ->where('Category', 'TR')
            ->where('Work_Content', 'Translate')
            ->where('delete_time',0)
            //->whereBetweenTime('Work_Date', $s, $d)
            ->where('Percentage_Completed', '<=', 10)
            //->order('Work_Date desc')
            ->group('Work_Date,Filing_Code')
            ->select();


        $jd_c['pages'] = 0;
        if(!empty($jd_c_f)){
            foreach ($jd_c_f as $k => $v){
                $jd_c['pages'] += $v['pages'];
            }
        }


        if(!empty($k_f)){
            $p_file_no = $k_f;
        }else{
            $p_file_no = $file_no;
        }

        // 项目汇总 未交稿 且 进度数据没有
        $p_jd_c_f = Db::name('pj_contract_review')
            ->field('Filing_Code,SUM(Pages) pages')
            ->where('Filing_Code', 'in', $p_file_no)
            ->where('Delivered_or_Not','No')
            ->where('delete_time',0)
            ->group('Filing_Code')
            ->select();

        $p_jd_c['pages'] = 0;
        if(!empty($p_jd_c_f)){
            foreach ($p_jd_c_f as $k => $v){
                $p_jd_c['pages'] += $v['pages'];
            }
        }
   

        /*
         * 各进度的页数
         * */
        // 50%以上页数
        $arr['a_Total_Pages_Process'] = $jd_a['pages'];
        // 10-50%页数
        $arr['b_Total_Pages_Process'] = $jd_b['pages'];

        // 未开始 10%以下
        if(!empty($k_f)){ // 交集为空 等于 项目汇总数据

            $arr['c_Total_Pages_Translated'] = $p_jd_c['pages'];

        }else if(!empty($jd_f)){ //

            $arr['c_Total_Pages_Translated'] = $jd_c['pages'] + $p_jd_c['pages'];

        }else{
            $arr['c_Total_Pages_Translated'] = $jd_c['pages'];
        }
        

        // 待提交 总页数
//        $arr['Total_Pages_submitted'] = $arr['a_Total_Pages_Process'] + $arr['b_Total_Pages_Process'] + $arr['c_Total_Pages_Translated'];
        $arr['Total_Pages_submitted'] = Db::name('pj_contract_review')->where('Delivered_or_Not', 'No')->where('delete_time',0)->sum('pages');



        /*
         * 翻译、校对、排版 人数统计
         * */
        $Translator = Db::name('admin')
            ->where('job_id', 10)
            ->where('status', 0)
            ->where('trainee', 0)
            ->where('delete_time',0)
            ->count('id');

        // 翻译 实习生
        $Translator_sxs = Db::name('admin')
            ->where('job_id', 10)
            ->where('status', 0)
            ->where('trainee', 1)
            ->where('delete_time',0)
            ->count('id');

        $Reviser = Db::name('admin')
            ->where('job_id', 11)
            ->where('status', 0)
            ->where('delete_time',0)
            ->count('id');

        // 排版（预排、后排）
        $Formatter = Db::name('admin')
            ->where('job_id', 'in', [12,13])
            ->where('status', 0)
            ->where('delete_time',0)
            ->count('id');

        /*
         * 周 平均产能计算
         * */
        // 自定义数值 查询
        $info['t_score'] = Db::name('tj_diy_num')->where('id',1)->value('score');
        $info['r_score'] = Db::name('tj_diy_num')->where('id',2)->value('score');
        $info['f_score'] = Db::name('tj_diy_num')->where('id',3)->value('score');
        $info['s_score'] = Db::name('tj_diy_num')->where('id',4)->value('score');

        // 翻译
        $WCP_T = ($info['t_score'] * $Translator + $info['s_score'] * $Translator_sxs) /4.3;
        // 校对
        $WCP_R = $info['r_score'] * $Reviser /4.3;
        // 排版
        $WCP_F = $info['f_score'] * $Formatter /4.3;


        /*
         * 按格式组装返回数据
         * */
        // 页数、词数
        $arr['e_c']['p'] = $e_c['Pages']?$e_c['Pages']:0;
        $arr['e_c']['w'] = $e_c['Word']?$e_c['Word']:0;

        $arr['c_e']['p'] = $c_e['Pages']?$c_e['Pages']:0;
        $arr['c_e']['w'] = $c_e['Word']?$c_e['Word']:0;

        $arr['o_c']['p'] = $o_c['Pages']?$o_c['Pages']:0;
        $arr['o_c']['w'] = $o_c['Word']?$o_c['Word']:0;

        $arr['c_o']['p'] = $c_o['Pages']?$c_o['Pages']:0;
        $arr['c_o']['w'] = $c_o['Word']?$c_o['Word']:0;

        // 翻译总页数
        $arr['t_pages'] = $arr['e_c']['p'] + $arr['c_e']['p'] + $arr['o_c']['p'] + $arr['c_o']['p'];

        // 人员数量
        $arr['Translator_Codex'] = $Translator;
        $arr['Reviser_Codex'] = $Reviser;
        $arr['Formatter_Codex'] = $Formatter;
        $arr['Trainees'] = $Translator_sxs;

        // 小计
        $arr['Sub_Total'] = $Translator + $Reviser + $Formatter;
        // 总计
        $arr['Grand_Total'] = $Translator + $Reviser + $Formatter + $Translator_sxs;

        // 周 平均产能 保留2位小数
        $arr['WCP_T'] = round($WCP_T, 2);
        $arr['WCP_R'] = round($WCP_R, 2);
        $arr['WCP_F'] = round($WCP_F, 2);

        // （总） 平均产能
        $arr['Average_Capacity_Pages'] = round(($WCP_T + $WCP_R +$WCP_F)/3 , 2);

        /*
         * 预计完成天数计算
         * */

        // 分母不能為0
        if($WCP_T == 0){
            $arr['a_Estimated_days_finish'] = 0;
            $arr['b_Estimated_days_finish'] = 0;
        }else{
            // 完成50%以上 预计完成天数
            $arr['a_Estimated_days_finish'] = round($arr['a_Total_Pages_Process']/6/($WCP_T/3)*6 , 2);
            // 完成10-50% 预计完成天数
            $arr['b_Estimated_days_finish'] = round($arr['b_Total_Pages_Process']/1.5/$WCP_T , 2);
        }

        // 未开始的 预计完成天数
        if($arr['Average_Capacity_Pages'] == 0){
            $arr['c_Estimated_days_finish'] = 0;
        }else{
            $arr['c_Estimated_days_finish'] = round($arr['c_Total_Pages_Translated']/($arr['Average_Capacity_Pages']/3)*6 , 2);
        }

        // 预计完成 总 天数 A + B + C
        $arr['Total_days_works'] = $arr['a_Estimated_days_finish'] + $arr['b_Estimated_days_finish'] + $arr['c_Estimated_days_finish'];

        $a['code'] = 0;
        $a['msg'] = '成功';
        $a['data'][] = $arr;


        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['type'=>$type, 'time'=>$time]);
        }

        // 返回接口数据
        return json($a);
    }


    // 产能自定义 计算参数 编辑视图
    public function diy_num()
    {

        $info['t_score'] = Db::name('tj_diy_num')->where('id',1)->value('score');

        $info['r_score'] = Db::name('tj_diy_num')->where('id',2)->value('score');

        $info['f_score'] = Db::name('tj_diy_num')->where('id',3)->value('score');

        $info['s_score'] = Db::name('tj_diy_num')->where('id',4)->value('score');

        // 表单视图
        return view('', ['info'=>$info]);
    }

    // 产能自定义 计算参数 保存
    public function save_score(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        Db::name('tj_diy_num')->where('id',1)->update(['score'=>$data['t_score']]);

        Db::name('tj_diy_num')->where('id',2)->update(['score'=>$data['r_score']]);

        Db::name('tj_diy_num')->where('id',3)->update(['score'=>$data['f_score']]);

        Db::name('tj_diy_num')->where('id',4)->update(['score'=>$data['s_score']]);

        // 返回操作结果
        return json(['msg' => '更新成功']);
    }

}
