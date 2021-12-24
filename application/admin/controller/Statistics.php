<?php
namespace app\admin\controller;

use app\facade\Admin as AdminModel;
use app\facade\TjQaReviser as TjQaReviserModel;
use think\Controller;
use think\Request;
use think\Db;
use think\Env;

// 数据统计 控制器
class Statistics extends Controller
{
    // 首页 报表列表
    public function index()
    {
        // 用户id
        $id = session('administrator')['id'];

        // 查询用户可见 报表栏
        $report = AdminModel::getOne($id, ['report_arr']);

        // 字符串分割为数组
        $id_arr = explode(',' , $report['report_arr']);

        // 查询详情
        $report_menu = Db::name('xt_report')->where('id', 'in', $id_arr)->select();

        // 返回视图
        return view('', ['report_menu'=>$report_menu]);
    }


    /*
     * 翻译量对比统计   （最近三年）               文件编号（创建时间）
     * @param   int               $y              限定年份
     * @param   string            $field          搜索字段
     * @param   string            $keyword        搜索关键词
     * @param   int               $limit          每页显示数据条数
     * @return Paginator
     * @throws \think\exception\Exception
     * */
    public function translation(Request $request, $y = '', $field = '', $keyword = '', $limit = 50)
    {

        // 前年
        $f_year = date("Y", strtotime("-2 year"));
        // 去年
        $s_year = date("Y", strtotime("-1 year"));
        // 今年
        $year = date('Y');  $arr = [];  $c_arr = [];

        // 如果没有传参 默认当前年份数据
        if($y == '') {
            $y = $year;
        }

        // 年份数组
        $y_arr = [$f_year, $s_year, $year];

        // 预定义 月份数组
        $m = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        // 预定义 字段下标
        $b = ['a','b','c','d','e','f','g','h','i','j','k','l'];

        // 每年 的第一天
        $ys = intval($y. '0101');
        // 每年 最后一天
        $yd = intval($y. '1231');

        // 查询 项目汇总表 中 按公司分组 的 公司数组  (限定年份)
        $company_arr = Db::name('pj_contract_review')
            ->field('Company_Name')
            ->where($field, 'like', "%$keyword%")
            ->where('delete_time',0)
            ->whereBetweenTime('Date', $ys, $yd)
            ->group('Company_Name')
            ->paginate($limit);

        //遍历 查询 每个公司 每个月 的 页数 （可带参数 y）
        for ($i = 0; $i < 12; $i++) {

            // 时间为条件
            $s = intval($y. $m[$i] . '01');
            $d = intval($y. $m[$i] . '31');

            // 文件编号 模糊查询 可能会有误差
            $t = 'F-' .$y.$m[$i];

            foreach ($company_arr as $k => $v){

                $list_date = Db::name('pj_contract_review')
                    ->field('SUM(Pages) Pages')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('delete_time',0)
                    //->where('Filing_Code', 'like', $t.'%')
                    ->whereBetweenTime('Date', $s, $d)
                    ->group('Company_Name')
                    ->select();

                $arr[$k]['Company_Name'] = $v['Company_Name'];

                if(!empty($list_date)){
                    $arr[$k]['Page'][$b[$i]] = intval($list_date[0]['Pages']);
                }else{
                    $arr[$k]['Page'][$b[$i]] = 0;
                }

                $arr[$k]['Total'] = array_sum($arr[$k]['Page']);
            }

            foreach ($y_arr as $k => $v){

                // 时间为条件
                $cs = intval($v. $m[$i] . '01');
                $cd = intval($v. $m[$i] . '31');

                // 文件编号 模糊查询 可能会有误差
                $y_t = 'F-' .$v.$m[$i];

                $c_date[$m[$i]] = Db::name('pj_contract_review')
                    ->field('SUM(Pages) Pages')
                    ->where('delete_time',0)
                    //->where('Filing_Code', 'like', $y_t.'%')
                    ->whereBetweenTime('Date', $cs, $cd)
                    ->select();

                $c_arr[$k]['year'] = $v;

                if(!empty($c_date)){
                    $c_arr[$k]['Page'][$i +1] = intval($c_date[$m[$i]][0]['Pages']);
                }else{
                    $c_arr[$k]['Page'][$i +1] = 0;
                }

                $c_arr[$k]['Total'] = array_sum($c_arr[$k]['Page']);
            }
        }

        // 按格式组装 列表数据
        if(empty($arr)){
            $a['count'] = 0;
        }else{
            $a['count'] = $company_arr->total();
        }
        $a['code'] = 0;
        $a['msg'] = '成功';
        $a['data'] = $arr;

        //预定义 图表数据
        $chart_data = [];

        // 遍历获取 中间 月份 数据
        foreach ($c_arr as $k => $v){

            for ($i = 0; $i < 12; $i++) {
                $chart_data[$i+1][0] = ($i + 1) . '月';
                $chart_data[$i+1][1] = $c_arr[0]['Page'][$i+1];
                $chart_data[$i+1][2] = $c_arr[1]['Page'][$i+1];
                $chart_data[$i+1][3] = $c_arr[2]['Page'][$i+1];
            }

            $chart_data['Total'][1] = $c_arr[0]['Total'];
            $chart_data['Total'][2] = $c_arr[1]['Total'];
            $chart_data['Total'][3] = $c_arr[2]['Total'];
        }

        // 表头 字段
        $h = ['Pages', $f_year, $s_year, $year];

        // 插入 表头 首项
        array_unshift($chart_data, $h);

        // 插入 表尾 合计
        $chart_data['Total'][0] = 'Total';

        // 去除 键值下标 (因为图表数据格式要求 纯数值不带键值)
        $chart_data = array_values($chart_data);
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'y'=>$y, 'chart_data'=>json_encode($chart_data), 'a'=>json_encode($a),
                'f_year'=>$f_year, 's_year'=>$s_year, 'year'=>$year
            ]);
        }

        // 返回接口数据
        return json($a);
    }

    /*
    // 翻译金额对比统计 （最近两年）开票日期
    // (结算管理表中 的 开票金额)
    */
    public function translation_amount(Request $request, $field = '', $keyword = '', $limit = 50)
    {
        // 去年
        $s_year = date("Y", strtotime("-1 year"));
        // 今年
        $year = date('Y');  $arr = [];

        // 年份数组
        $y_arr = ['s_year'=>$s_year, 'year'=>$year];

        // 预定义 月份数组
        $m = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        // 预定义 字段下标
        $b = ['a','b','c','d','e','f','g','h','i','j','k','l'];
        $c = ['A','B','C','D','E','F','G','H','I','J','K','L'];

        // 去年 的第一天
        $ys = intval($s_year. '0101');
        // 今年 最后一天
        $yd = intval($year. '1231');

        // 查询 结算管理表 中 按公司分组 的 公司数组  (限定年份)
        $company_arr = Db::name('mk_invoicing')
            ->field('Company_Full_Name')
            ->where($field, 'like', "%$keyword%")
            ->where('delete_time',0)
            ->whereBetweenTime('Fapiao_Date', $ys, $yd)
            ->group('Company_Full_Name')
			->paginate($limit);

        /*
        // 遍历查询
        // 开票表中 近两年 每个公司 每个月 的
        // 开票金额 (保留两位小数)
        */
        for ($i = 0; $i < 12; $i++) {

            foreach ($y_arr as $key => $y){

                // 每年 每月 的第一天
                $s = intval($y . $m[$i] . '01');

                // 每年 每月 最后一天
                $d = intval($y . $m[$i] . '31');

                foreach ($company_arr as $k => $v){

                    $list_date = Db::name('mk_invoicing')
                        ->field('CAST(SUM(Fapiao_Amount) as decimal(15,2)) as money')
                        ->where('Company_Full_Name', $v['Company_Full_Name'])
                        ->where('delete_time',0)
                        ->whereBetweenTime('Fapiao_Date', $s, $d)
                        ->group('Company_Full_Name')
                        ->select();

                    $arr[$k]['year'] = $y;
                    $arr[$k]['Company_Name'] = $v['Company_Full_Name'];

                    if(!empty($list_date)){

                        if($key == 's_year'){
                            $arr[$k]['money'][$b[$i]] = $list_date[0]['money'];

                            $arr[$k][$y][$b[$i]] = $list_date[0]['money'];

                        }else{
                            $arr[$k]['money'][$c[$i]] = $list_date[0]['money'];

                            $arr[$k][$y][$c[$i]] = $list_date[0]['money'];
                        }
                    }else{

                        if($key == 's_year') {
                            $arr[$k]['money'][$b[$i]] = 0;

                            $arr[$k][$y][$b[$i]] = 0;
                        }else{
                            $arr[$k]['money'][$c[$i]] = 0;

                            $arr[$k][$y][$c[$i]] = 0;
                        }
                    }
                }
            }

            /*计算每月 今年-去年 差值*/
            foreach ($arr as $j => $z){
                $arr[$j]['DV'][$b[$i]] = $arr[$j]['money'][$c[$i]] - $arr[$j]['money'][$b[$i]];
            }
        }

        //halt($arr);

        // 合计年度值 计算年度总差值
        foreach ($arr as $k => $v){

            $arr[$k]['t_s_year'] = array_sum($arr[$k][$y_arr['s_year']]);
            $arr[$k]['t_year'] = array_sum($arr[$k][$y_arr['year']]);

            $arr[$k]['t_DV'] = $arr[$k]['t_year'] - $arr[$k]['t_s_year'];
        }



        // 按格式组装 列表数据
        if(empty($arr)){
            $a['count'] = 0;
        }else{
            $a['count'] = $company_arr->total();
        }
        $a['code'] = 0;
        $a['msg'] = '成功';
        $a['data'] = $arr;
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['s_year'=>$s_year, 'year'=>$year, 'a'=>json_encode($a)]);
        }

        // 返回接口数据
        return json($a);
    }



    // 年度翻译量汇总
    // Annual Production Volume


    /*
    // 销售人员销售额汇总（最近三年）
    // (结算管理表中 的 付款完成 未税金额)
    */
    public function sales_statistics(Request $request, $y = '')
    {
        // 前年
        $f_year = date("Y", strtotime("-2 year"));
        // 去年
        $s_year = date("Y", strtotime("-1 year"));
        // 今年
        $year = date('Y');

        // 如果没有传参 默认当前年份数据
        if($y == '') {
            $y = $year;
        }

        // 每年的第一天
        $s = intval($y. '0101');
        // 每年最后一天
        $d = intval($y. '1231');

        // 按 销售人员 分组统计
        $arr = Db::name('mk_invoicing')
            ->field('Sales, SUM(Net_Amount) money')
            ->where('delete_time',0)
            ->where('Status', '付款完成')
            ->whereBetweenTime('Date_of_Balance', $s, $d)
            ->order('money desc')
            ->group('Sales')
            ->select();

        // 按格式组装返回数据
        $a['code'] = 0;
        $a['msg'] = '成功';
        $a['data'] = $arr;

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['y'=>$y, 'f_year'=>$f_year, 's_year'=>$s_year, 'year'=>$year]);
        }

        // 返回接口数据
        return json($a);
    }


    // 工作绩效统计
    // performance_table


    /*
     * 翻译人员质量评估           （允许范围 最近三年、四个季度、12个月）
     * @param   string            $type           时间类型（M：月份 Q：季度 Y：年份）
     * @param   string            $time           时间值（如：202006、Q1、2020）
     * @param   string            $field          搜索字段
     * @param   string            $keyword        搜索关键词
     * @param   int               $limit          每页显示数据条数
     * @return Paginator
     * @throws \think\exception\Exception
     * */
    public function qa_translator(Request $request, $type = '', $time = '', $field = '', $keyword = '', $limit = 50)
    {
        // 前年
        $f_year = date("Y", strtotime("-2 year"));
        // 去年
        $s_year = date("Y", strtotime("-1 year"));
        // 今年
        $year = date('Y'); $arr = []; $t_arr = [];
        $s = ''; $d = '';
        // 查询条件 判断
        if($type == ''){
            // 为空时 默认当前 年月
            $type = 'M';
            $time = date('m');

            $s = intval(date('Ym')  . '01');
            //$d = intval(date('Ym')  . '31');
        }else{
            // 判断时间类型
            switch ($type) {
                case 'M':
                    $s = intval(date('Y') . $time . '01');
                    //$d = intval(date('Y') . $time . '31');
                    break;
                case 'Q':
                    // 判断季度
                    switch ($time) {
                        case 'Q1':
                            $s = intval(date('Y') . '0101');
                            //$d = intval(date('Y') . '0331');
                            break;
                        case 'Q2':
                            $s = intval(date('Y') . '0401');
                            //$d = intval(date('Y') . '0631');
                            break;
                        case 'Q3':
                            $s = intval(date('Y') . '0701');
                            //$d = intval(date('Y') . '0931');
                            break;
                        case 'Q4':
                            $s = intval(date('Y') . '1001');
                            //$d = intval(date('Y') . '1231');
                            break;
                    }
                    break;
                case 'Y':
                    //$s = intval($time . '0101');
                    //$d = intval($time . '1231');
                    if( isset(explode('-',$time)[1]) ){ //选择了月份
                        $s = intval(explode('-',$time)[0].explode('-',$time)[1].'01');
                        //$d = intval(date('YmdHis', strtotime("$s +1 month -1 second")));

                    }else{ //整年
                        $s = intval($time . '0101');
                        //$d = intval($time . '1231');
                    }
                    break;
            }
        }
        $d = intval(date('YmdHis', strtotime("$s +1 month -1 second")));//获取月份结束精确到年月日时分秒

        // 格式时间转 时间戳
        $s = strtotime($s);
        $d = strtotime($d);

        //翻译评估表  按人员姓名 分组
        $t_arr = Db::name('pj_translation_evaluation')
            ->field('Translator')
            ->where($field, 'like', "%$keyword%")
            ->whereBetweenTime('create_time', $s, $d)
            ->where('delete_time',0)
            ->group('Translator')
            ->paginate($limit);


        // 评价等级 数组
        $p = ['A','B','C','D'];
        $pl = count($p);

        // 分组 查询 整体评价 为 ABCD 各有多少个
        for($n = 0; $n < $pl; $n++) {

            foreach ($t_arr as $k => $v){

                $arr[$v['Translator']][$p[$n]] =
                    Db::name('pj_translation_evaluation')
                        ->field("count(Overall_Evaluation) $p[$n]")
                        ->where('Translator', $v['Translator'])
                        ->where('Overall_Evaluation', $p[$n])
                        ->whereBetweenTime('create_time', $s, $d)
                        ->where('delete_time',0)
                        ->group('Translator')
                        ->find();
            }
        }
        //dump($arr);

        // 计算 ABCD 各自的总数  总评价数(数据条数)
        foreach ($arr as $key => $val){
            foreach ($val as $k => $v) {
                for ($n = 0; $n < $pl; $n++) {
                    if ($v == NULL) {
                        $arr[$key][$k] = 0;
                    } else {
                        $arr[$key][$k] = array_values($v);
                        $arr[$key][$k] = $arr[$key][$k][0];
                    }
                }
            }

            $arr[$key]['name'] = $key;

            $arr[$key]['total'] = array_sum($arr[$key]);

            if($arr[$key]['C'] >= ($arr[$key]['total']/2) or $arr[$key]['D'] >= ($arr[$key]['total']/2)){

                $arr[$key]['result'] = '质量不可接受';

            } elseif ($arr[$key]['A'] > ($arr[$key]['total']/2) && $arr[$key]['C'] == 0 && $arr[$key]['D'] == 0){

                $arr[$key]['result'] = '质量优秀';
            }else{
                $arr[$key]['result'] = '质量可接受';
            }
        }
        //halt($arr);

        // 去掉 键值 只保留数据
        $arr = array_values($arr);

        // 按格式组装 列表数据
        if(empty($arr)){
            $a['count'] = 0;
        }else{
            $a['count'] = $t_arr->total();
        }
        $a['code'] = 0;
        $a['msg'] = '成功';
        $a['data'] = $arr;
        //$a['sql'] = Db::name('pj_translation_evaluation')->getLastSql();

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['type'=>$type, 'time'=>$time, 'keyword'=>$keyword,
                'f_year'=>$f_year, 's_year'=>$s_year, 'year'=>$year]);
        }

        // 返回接口数据
        return json($a);
    }

    // 校对人员质量评估 (qa_reviser手填、增删改查)

    //排版人员质量评估 (预排、后排)
    public function qa_formatter(Request $request, $type = '', $time = '', $keyword = '', $limit = 50)
    {
        // 前年
        $f_year = date("Y", strtotime("-2 year"));
        // 去年
        $s_year = date("Y", strtotime("-1 year"));
        // 今年
        $year = date('Y'); $arr = []; $t_arr = [];
        $s = ''; $d = '';
        // 查询条件 判断
        if($type == ''){
            // 为空时 默认当前 年月
            $type = 'M';
            $time = date('m');

            $s = intval(date('Ym')  . '01');
            //$d = intval(date('Ym')  . '31');
        }else{
            // 判断时间类型
            switch ($type) {
                case 'M':
                    $s = intval(date('Y') . $time . '01');
                    //$d = intval(date('Y') . $time . '31');
                    break;
                case 'Q':
                    // 判断季度
                    switch ($time) {
                        case 'Q1':
                            $s = intval(date('Y') . '0101');
                            //$d = intval(date('Y') . '0331');
                            break;
                        case 'Q2':
                            $s = intval(date('Y') . '0401');
                            //$d = intval(date('Y') . '0631');
                            break;
                        case 'Q3':
                            $s = intval(date('Y') . '0701');
                            //$d = intval(date('Y') . '0931');
                            break;
                        case 'Q4':
                            $s = intval(date('Y') . '1001');
                            //$d = intval(date('Y') . '1231');
                            break;
                    }
                    break;
                case 'Y':
                    /*$s = intval($time . '0101');
                    $d = intval($time . '1231');*/
                    if( isset(explode('-',$time)[1]) ){ //选择了月份
                        $s = intval(explode('-',$time)[0].explode('-',$time)[1].'01');
                        //$d = intval(date('YmdHis', strtotime("$s +1 month -1 second")));

                    }else{ //整年
                        $s = intval($time . '0101');
                        //$d = intval($time . '1231');
                    }
                    break;
            }
        }
        $d = intval(date('YmdHis', strtotime("$s +1 month -1 second")));//获取月份结束精确到年月日时分秒

        // 格式时间转 时间戳
        $s = strtotime($s);
        $d = strtotime($d);

        //预排、后排 评估表  按人员姓名 分组
        $yp_arr = Db::name('pj_y_p_evaluation')
            ->field('Pre_Formatter')
            ->where('Pre_Formatter', 'like', "%$keyword%")
            ->whereBetweenTime('create_time', $s, $d)
            ->where('delete_time',0)
            ->group('Pre_Formatter')
            ->paginate($limit);

        $hp_arr = Db::name('pj_h_p_evaluation')
            ->field('Post_Formatter')
            ->where('Post_Formatter', 'like', "%$keyword%")
            ->whereBetweenTime('create_time', $s, $d)
            ->where('delete_time',0)
            ->group('Post_Formatter')
            ->paginate($limit);


        // 评价等级 数组
        $p = ['A','B','C','D'];
        $pl = count($p);

        // 分组 查询 整体评价 为 ABCD 各有多少个

        for($n = 0; $n < $pl; $n++) {

            foreach ($yp_arr as $k => $v){
                $arr[$v['Pre_Formatter']][$p[$n]] =
                    Db::name('pj_y_p_evaluation')
                        ->field("count(Overall_Evaluation) $p[$n]")
                        ->where('Pre_Formatter', $v['Pre_Formatter'])
                        ->where('Overall_Evaluation', $p[$n])
                        ->whereBetweenTime('create_time', $s, $d)
                        ->where('delete_time',0)
                        ->group('Pre_Formatter')
                        ->find();

                $arr[$v['Pre_Formatter']]['cate'] = 'YP';
            }

//            foreach ($hp_arr as $key => $val){
//                $arr[$val['Post_Formatter']][$p[$n]] =
//                    Db::name('pj_h_p_evaluation')
//                        ->field("count(Overall_Evaluation) $p[$n]")
//                        ->where('Post_Formatter', $val['Post_Formatter'])
//                        ->where('Overall_Evaluation', $p[$n])
//                        ->whereBetweenTime('create_time', $s, $d)
//                        ->where('delete_time',0)
//                        ->group('Post_Formatter')
//                        ->find();
//
//                $arr[$val['Post_Formatter']]['cate'] = 'HP';
//            }
        }
//        halt($arr);

        // 计算 ABCD 各自的总数 总评价数（数据条数）
        foreach ($arr as $key => $val){
            $total = Db::name('pj_y_p_evaluation')
                ->where('Pre_Formatter', $key)
                ->whereBetweenTime('create_time', $s, $d)
                ->where('delete_time',0)
                ->count();
//            if($arr[$key]['cate'] == 'YP'){
//
//            }else{
//                $total = Db::name('pj_h_p_evaluation')
//                    ->where('Post_Formatter', $key)
//                    ->whereBetweenTime('create_time', $s, $d)
//                    ->where('delete_time',0)
//                    ->count();
//            }

            foreach ($val as $k => $v) {

                if($k == 'cate') {
                    unset($arr[$key]['cate']);
                }else{
                    for ($n = 0; $n < $pl; $n++) {

                        if ($v == NULL) {
                            $arr[$key][$k] = 0;
                        } else {
                            $arr[$key][$k] = array_values($v);
                            $arr[$key][$k] = $arr[$key][$k][0];
                        }
                    }
                }
            }

            $arr[$key]['name'] = $key;

            $arr[$key]['total'] = $total;

            if($arr[$key]['C'] >= ($arr[$key]['total']/2) or $arr[$key]['D'] >= ($arr[$key]['total']/2)){

                $arr[$key]['result'] = '质量不可接受';

            } elseif ($arr[$key]['A'] > ($arr[$key]['total']/2) && $arr[$key]['C'] == 0 && $arr[$key]['D'] == 0){

                $arr[$key]['result'] = '质量优秀';
            }else{
                $arr[$key]['result'] = '质量可接受';
            }
        }
//        halt($arr);

        // 去掉 键值 只保留数据
        $arr = array_values($arr);

        // 按格式组装 列表数据
        if(empty($arr)){
            $a['count'] = 0;
        }else{
            $a['count'] = $yp_arr->total() + $hp_arr->total();
        }
        $a['code'] = 0;
        $a['msg'] = '成功';
        $a['data'] = $arr;
        //$a['sql'] = Db::name('pj_translation_evaluation')->getLastSql();

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['type'=>$type, 'time'=>$time, 'keyword'=>$keyword,
                'f_year'=>$f_year, 's_year'=>$s_year, 'year'=>$year]);
        }

        // 返回接口数据
        return json($a);
    }

    //质量分析 （翻译评估表）
    // QualityAnalysis

     //翻译、校对人员综合考核
    // OpaTrRe

     //排版人员综合考核
    // OpaFormatter

    //项目助理综合考核
    // OpaPa

    //项目通道
    // ProjectFunnel


    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 文件编码 获取相关信息(项目名称、文件名称)
        $info = db('pj_project_profile')
            ->field('Project_Name as Sampled_project, Job_Name as Sampled_document')
            ->where('Filing_Code', $code)->find();

        // 返回值
        return json(['data'=>$info]);
    }

    // 显示新建的表单页
    public function create($table)
    {
        // 数据库表字段集
        $colsData = getAllField($table);

        return view('',['info'=>'', 'table' => $table, 'field'=>$colsData]);
    }

    //编辑视图
    public function edit($table, $id)
    {

        // 数据库表字段集
        $colsData = getAllField($table);

        // 查询结果

        return view('',['info'=>'', 'field'=>$colsData]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 数据库表
        $table = $data['table_name'];

        // 数据库表字段集
        $colsData = getAllField($table);

        // 权重查询
        $score_arr = Db::name('tj_score')
            ->field('score_field, score')
            ->where('table_name', $table)
            ->select();

        // 不参与加计算的字段
        $a = ['id','Time','Name','Total_score'];

        // 拼装数组（权重设置 不参与计算的默认为空 未设置的默认按100%）
        foreach ($colsData as $k => $v){

            if(in_array($colsData[$k]['Field'],$a)){
                $colsData[$k]['score_field'] = '';
            }else{
                $colsData[$k]['score_field'] = 100;
            }

            foreach ($score_arr as $key => $val){

                if($colsData[$k]['Field'] == $val['score_field']){

                    $colsData[$k]['score_field'] = $val['score'];
                }
            }

            if(in_array($colsData[$k]['Field'],$a)){
                unset($colsData[$k]);
            }
        }

        $total = [];

        // 计算总分(评分项 * 权重比例)
        foreach ($colsData as $k => $v){

            $total[] = $data[$colsData[$k]['Field']] * $colsData[$k]['score_field']/100;
        }

        // 求和
        $data['Total_score'] = array_sum($total);

        // 移除非必要字段
        unset($data['table_name']);

        // 保存
        Db::table($table)->insert($data);

        // 返回操作结果
        return json(['msg' => '创建成功']);
    }



    public function pagesum()
    {
        $data=request()->param('month');

        if(isset($data)){
            $time= strtotime($data);
            $year = date("Y", $time);
            $month = date("m", $time);
            $day = date("d", $time);
            // 本月一共有几天
            $firstTime = mktime(0, 0, 0, $month, 1, $year);     // 创建本月开始时间
            $day = date('t',$firstTime);
            $lastTime = $firstTime + 86400 * $day  - 1; //结束时间戳
            $firstTime=intval(date("Ymd",$firstTime));
            $lastTime=intval(date("Ymd",$lastTime));
            if($data==''){
                $firstTime=19701201;
                $lastTime=20351201;
                $time= time();
                $year = date("Y", $time);

                $month = date("m", $time);

                $day = date("d", $time);
                // 本月一共有几天
                $firstTime = mktime(0, 0, 0, $month, 1, $year);     // 创建本月开始时间
                $day = date('t',$firstTime);
                $lastTime = $firstTime + 86400 * $day  - 1; //结束时间戳
                $firstTime=intval(date("Ymd",$firstTime));
                $lastTime=intval(date("Ymd",$lastTime));
            }
        }else{
            $time= time();
            $year = date("Y", $time);

            $month = date("m", $time);

            $day = date("d", $time);
            // 本月一共有几天
            $firstTime = mktime(0, 0, 0, $month, 1, $year);     // 创建本月开始时间
            $day = date('t',$firstTime);
            $lastTime = $firstTime + 86400 * $day  - 1; //结束时间戳
            $firstTime=intval(date("Ymd",$firstTime));
            $lastTime=intval(date("Ymd",$lastTime));

        }

//        dump($firstTime);
//        dump($lastTime);
//        die;
        //每天要完成多少页
        $mt=  Db::table('ky_pj_contract_review')->whereBetweenTime('Completed',$firstTime,$lastTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
            ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();
      //预排页数Work_Content
        $yp=Db::table('ky_pj_daily_progress_dtp')->whereBetweenTime('Work_Date',$firstTime,$lastTime)->where('delete_time',0)->where('Work_Content','Preformat')->field('Work_Date,sum(Number_of_Pages_Completed) as yppage')
            ->group('Work_Date')->select();
        //后排页数Work_Content
        $hp=Db::table('ky_pj_daily_progress_dtp')->whereBetweenTime('Work_Date',$firstTime,$lastTime)->where('delete_time',0)->where('Work_Content','Postformat')->field('Work_Date,sum(Number_of_Pages_Completed) as hppage')
            ->group('Work_Date')->select();
        //翻译页数Work_Content
        $tr=Db::table('ky_pj_daily_progress_tr_re')->whereBetweenTime('Work_Date',$firstTime,$lastTime)->where('delete_time',0)->wherein('Work_Content',['Translate','TR Modify','TR Finalize'])->where('Category','TR')
            ->field('Work_Date,sum(Number_of_Pages_Completed) as trpage')
            ->group('Work_Date')->select();
        //校对页数Work_Content
        $xd=Db::table('ky_pj_daily_progress_tr_re')->whereBetweenTime('Work_Date',$firstTime,$lastTime)->where('delete_time',0)->wherein('Work_Content',['Revise','RE Modify','RE (Sampling)','RE (Highlight)','RE (Sampling_Highlight)','RE Finalize'])->where('Category','RE')
            ->field('Work_Date,sum(Number_of_Pages_Completed) as xdpage')
            ->group('Work_Date')->select();
        foreach($mt as $k=>$v){
            $mt[$k]['Work_Date']=$v['Completed'];
            unset($mt[$k]['Completed']);
        }
        $hb=[];
        $c = array_merge($hp,$yp,$mt,$tr,$xd);

        foreach ($c as $k1=>$v1)
        {
            if(isset($v1['yppage'])){
                $hb[$v1['Work_Date']]['yppage']= intval($v1['yppage']);
            }
            if(isset($v1['hppage'])){
                $hb[$v1['Work_Date']]['hppage']= intval($v1['hppage']);
            }
            if(isset($v1['trpage'])){
                $hb[$v1['Work_Date']]['trpage']= intval($v1['trpage']);
            }
            if(isset($v1['xdpage'])){
                $hb[$v1['Work_Date']]['xdpage']= intval($v1['xdpage']);
            }
            if(isset($v1['sumpage'])){
                $hb[$v1['Work_Date']]['sumpage']= intval($v1['sumpage']);
            }

        }
        $list=[];
        foreach ($hb as $k2=>$v)
        {
            $hb[$k2]['date']=$k2;
            $list[]=$hb[$k2];
        }
        if(!isset($list)){
             return '无数据';
        }

        $id=[];
        foreach ($list as $key => $row) {
            $id[$key]  = $row['date'];
        }
        array_multisort($id, SORT_DESC , $list);

        $mon=[];
        foreach ($list as $k=>$v)
        {
            $mon[]=$v['date'];
        }

        $char=[];
        foreach ($list as $k=>$v)
        {
            unset($v['hppage']);
            unset($v['yppage']);
            unset($v['trpage']);
            unset($v['xdpage']);
            unset($v['date']);
            if(!isset($v['sumpage']))
            {
                $v['sumpage']=0;
            }else{
                $v['sumpage']=intval( $v['sumpage']);
            }
            $char['name']='页数';
            $char['type']= 'bar';
            $char['data'][]=$v['sumpage'];
        }
        if (!request()->isAjax()) {
            $this->assign(['list'=>$list,'mon'=>json_encode($mon),'char'=>json_encode($char)]);
            return $this->fetch();
        }
        return [
            'code'  => 0,
            'msg'   => '',
            'count' => 0,
            'data'  =>$list,
        ];

    }


    public function pa()
    {
        $data=request()->param('month');

        if(isset($data)){
            $time= strtotime($data);
            $year = date("Y", $time);
            $month = date("m", $time);
            $day = date("d", $time);
            // 本月一共有几天
            $firstTime = mktime(0, 0, 0, $month, 1, $year);     // 创建本月开始时间
            $day = date('t',$firstTime);
            $lastTime = $firstTime + 86400 * $day  - 1; //结束时间戳
            $firstTime=intval(date("Ymd",$firstTime));
            $lastTime=intval(date("Ymd",$lastTime));
            if($data==''){

                $time= time();
                $year = date("Y", $time);

                $month = date("m", $time);

                $day = date("d", $time);
                // 本月一共有几天
                $firstTime = mktime(0, 0, 0, $month, 1, $year);     // 创建本月开始时间
                $day = date('t',$firstTime);
                $lastTime = $firstTime + 86400 * $day  - 1; //结束时间戳
                $firstTime=intval(date("Ymd",$firstTime));
                $lastTime=intval(date("Ymd",$lastTime));
            }
        }else{
            $time= time();
            $year = date("Y", $time);

            $month = date("m", $time);

            $day = date("d", $time);
            // 本月一共有几天
            $firstTime = mktime(0, 0, 0, $month, 1, $year);     // 创建本月开始时间
            $day = date('t',$firstTime);
            $lastTime = $firstTime + 86400 * $day  - 1; //结束时间戳
            $firstTime=intval(date("Ymd",$firstTime));
            $lastTime=intval(date("Ymd",$lastTime));
        }

        $pa=  Db::table('ky_pj_contract_review')->where('Delivered_or_Not','<>','CXL')->where('delete_time',0)->where('PA','<>','')->whereBetweenTime('Date',$firstTime,$lastTime)->field('PA,sum(Pages) as sumpage,count(id) as num')->group('PA')->select();
        $pa2=  Db::table('ky_pj_contract_review')->where('Delivered_or_Not','=','No')->where('delete_time',0)->where('PA','<>','')->whereBetweenTime('Date',19701201,20351201)->field('PA,sum(Pages) as sumpage1,count(id) as num1')->group('PA')->select();
        $list=[];

        //dump($pa2);
        if (!request()->isAjax()) {
            $this->assign(['pa2'=>$pa2]);
            return $this->fetch();
        }
        return [
            'code'  => 0,
            'msg'   => '',
            'count' => 0,
            'data'  =>$pa,
        ];
    }
}
