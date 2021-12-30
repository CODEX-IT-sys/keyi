<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Env;

// 统计报表 年度翻译量汇总 （近两年 每月 接收、提交） 控制器
class Translation extends Controller
{

    /*
     * 年度翻译量汇总  Annual Production Volume
     * @param   int               $y                限定年份(限定近两年)
     * @param   int               $mon              限定月份(1-12)
     *
     * 接收           项目汇总    交付日期Completed
     * 提交           项目汇总    交付日期Completed       已交稿的
     * */
    public function index(Request $request, $y = '', $mon = '', $field = '', $keyword = '', $limit = 50)
    {

        // 前年
        $f_year = date("Y", strtotime("-2 year"));
        // 去年
        $s_year = date("Y", strtotime("-1 year"));
        // 今年
        $year = date('Y'); $q_year = '';

        // 今年接收、提交数组
        $js_arr = []; $tj_arr = [];
        // 去年接收、提交数组
        $q_js_arr = []; $q_tj_arr = [];

        /*
         * 时间参数设置，如果没有传参
         * 默认当前年份数据
         * */
        if($y == '') {$y = $year;}
        if($y == $year) {
            /*去年*/
            $q_year = $s_year;
        }
        //当前选中的是 去年 相对的去年就是前年
        if($y == $s_year){
            /*去年 为前年*/
            $q_year = $f_year;
        }

        // 如果没有传参 默认当前月份数据
        if($mon == '') {
            $mon = date('m');
        }

        /*参数 去年*/
        // 去年 的第一天
        $qys = intval($q_year. '0101');
        // 去年 最后一天
        $qyd = intval($q_year. '1231');

        /*参数 今年*/
        // 今年 的第一天
        $ys = intval($y. '0101');
        // 今年 最后一天
        $yd = intval($y. '1231');


        // 预定义 月份数组
        $m = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        // 预定义 字段下标
        $b = ['a','b','c','d','e','f','g','h','i','j','k','l'];


        /*去年*/
        /*
         * 接收 // 查询 项目汇总表中 中 按公司分组 的 公司数组  (限定年份)
         * */
        $q_js_company_arr = Db::name('pj_contract_review')
            ->field('Company_Name')
            ->where($field, 'like', "%$keyword%")
            ->where('delete_time',0)
            ->where('Delivered_or_Not','<>','CXL')
            ->whereBetweenTime('Date', $qys, $qyd)
            ->group('Company_Name')
            ->paginate($limit);


        /*
         * 提交 // 查询 项目汇总表中(交稿) 按公司分组 的 公司数组  (限定年份)
         * */
        /*
         * $q_tj_company_arr = Db::name('pj_contract_review')
            ->field('Company_Name')
            ->where($field, 'like', "%$keyword%")
            ->where('Delivered_or_Not','Yes')
            ->where('delete_time',0)
            ->whereBetweenTime('Completed', $qys, $qyd)
            ->group('Company_Name')
            ->paginate($limit);
        */


        //遍历 查询 每个公司 每个月 的项目 页数（贪婪匹配）
        for ($i = 0; $i < 12; $i++) {

            $qs = intval($q_year. $m[$i] . '01');
            $qd = intval($q_year. $m[$i] . '31');

            // 接收
            foreach ($q_js_company_arr as $k => $v){

                $q_js_date = Db::name('pj_contract_review')
                    ->field('SUM(Pages) Pages')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('delete_time',0)
                    ->where('Delivered_or_Not','<>','CXL')
                    ->whereBetweenTime('Date', $qs, $qd)
                    ->group('Company_Name')
                    ->select();

                $q_js_arr[$k]['Company_Name'] = $v['Company_Name'];

                if(!empty($q_js_date)){
                    $q_js_arr[$k]['Page'][$b[$i]] = intval($q_js_date[0]['Pages']);
                }else {
                    $q_js_arr[$k]['Page'][$b[$i]] = 0;
                }
            }

            // 提交 （以公司为下标）
            foreach ($q_js_company_arr as $k => $v){

                $q_tj_date = Db::name('pj_contract_review')
                    ->field('SUM(Pages) Pages')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('Delivered_or_Not','Yes')
                    ->where('delete_time',0)
                    ->whereBetweenTime('Completed',  $qs, $qd)
                    ->group('Company_Name')
                    ->select();

                $q_tj_arr[$k]['Company_Name'] = $v['Company_Name'];

                if(!empty($tj_date)){
                    $q_tj_arr[$k]['Page'][$b[$i]] = intval($q_tj_date[0]['Pages']);
                }else{
                    $q_tj_arr[$k]['Page'][$b[$i]] = 0;
                }
            }
        }

        $q_arr = [];
        if(empty($q_js_arr) or empty($q_tj_arr)){

            $q_mon_js = 0;
            $q_mon_tj = 0;

        }else{

            /*
             * 组装 数据
             * YTD 计算 （取数组 前几个月的值 求和 计算）
             * */
            foreach ($q_js_arr as $v => $j){

                foreach ($q_tj_arr as $vo => $t){

                    if($j['Company_Name'] == $t['Company_Name']){

                        $q_arr[$v]['Company_Name'] = $j['Company_Name'];

                        $q_m_js_arr[$v] = array_slice($q_js_arr[$v]['Page'], 0, $mon);
                        $q_m_tj_arr[$v] = array_slice($q_tj_arr[$vo]['Page'], 0, $mon);

                        $q_arr[$v]['q_mon_js'] = array_sum($q_m_js_arr[$v]);
                        $q_arr[$v]['q_mon_tj'] = array_sum($q_m_tj_arr[$v]);
                    }
                }
            }
            //halt($q_arr);
        }


        /*今年*/
        /*
         * 接收 // 查询 项目汇总表中 中 按公司分组 的 公司数组  (限定年份)
         * */
        $js_company_arr = Db::name('pj_contract_review')
            ->field('Company_Name')
            ->where($field, 'like', "%$keyword%")
            ->where('delete_time',0)
            ->where('Delivered_or_Not','<>','CXL')
            ->whereBetweenTime('Date', $ys, $yd)
            ->group('Company_Name')
            ->paginate($limit);


        /*
         * 提交 // 查询 项目汇总表中(交稿) 按公司分组 的 公司数组  (限定年份)
         * */
        /*
         * $tj_company_arr = Db::name('pj_contract_review')
            ->field('Company_Name')
            ->where($field, 'like', "%$keyword%")
            ->where('Delivered_or_Not','Yes')
            ->where('delete_time',0)
            ->whereBetweenTime('Completed', $ys, $yd)
            ->group('Company_Name')
            ->paginate($limit);
        */


        //遍历 查询 每个公司 每个月 的项目 页数、词数
        for ($i = 0; $i < 12; $i++) {

            $s = intval($y. $m[$i] . '01');
            $d = intval($y. $m[$i] . '31');

            /*接收*/
            foreach ($js_company_arr as $k => $v){

                // 页数
                $js_date = Db::name('pj_contract_review')
                    ->field('SUM(Pages) Pages')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('delete_time',0)
                    ->where('Delivered_or_Not','<>','CXL')
                    ->whereBetweenTime('Date', $s, $d)
                    ->group('Company_Name')
                    ->select();

                // 中-外
                $js_c_o = Db::name('pj_contract_review')
                    ->field('SUM(Source_Text_Word_Count) word')
                    ->where('Language', 'like', 'CN-%')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('delete_time',0)
                    ->where('Delivered_or_Not','<>','CXL')
                    ->whereBetweenTime('Date', $s, $d)
                    ->select();

                // 外-中
                $js_o_c = Db::name('pj_contract_review')
                    ->field('SUM(Source_Text_Word_Count) word')
                    ->where('Language', 'like', '%-CN')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('delete_time',0)
                    ->where('Delivered_or_Not','<>','CXL')
                    ->whereBetweenTime('Date', $s, $d)
                    ->select();

                $js_arr[$k]['Company_Name'] = $v['Company_Name'];

                if(!empty($js_date)){

                    $js_arr[$k]['Page'][$b[$i]] = intval($js_date[0]['Pages']);
                    $js_arr[$k]['cword'][$b[$i]] = intval($js_c_o[0]['word']);
                    $js_arr[$k]['oword'][$b[$i]] = intval($js_o_c[0]['word']);

                }else{

                    $js_arr[$k]['Page'][$b[$i]] = 0;
                    $js_arr[$k]['cword'][$b[$i]] = 0;
                    $js_arr[$k]['oword'][$b[$i]] = 0;
                }

                $js_arr[$k]['j_total'][$b[$i]] = $js_arr[$k]['cword'][$b[$i]] + $js_arr[$k]['oword'][$b[$i]];
            }

            /*提交*/
            foreach ($js_company_arr as $k => $v){

                // 页数
                $tj_date = Db::name('pj_contract_review')
                    ->field('SUM(Pages) Pages')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('Delivered_or_Not','Yes')
                    ->where('delete_time',0)
                    ->whereBetweenTime('Completed', $s, $d)
                    ->group('Company_Name')
                    ->select();

                // 中-外
                $tj_c_o = Db::name('pj_contract_review')
                    ->field('SUM(Source_Text_Word_Count) word')
                    ->where('Language', 'like', 'CN-%')
                    ->where('Delivered_or_Not','Yes')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('delete_time',0)
                    ->whereBetweenTime('Completed', $s, $d)
                    ->select();

                // 外-中
                $tj_o_c = Db::name('pj_contract_review')
                    ->field('SUM(Source_Text_Word_Count) word')
                    ->where('Language', 'like', '%-CN')
                    ->where('Delivered_or_Not','Yes')
                    ->where('Company_Name', $v['Company_Name'])
                    ->where('delete_time',0)
                    ->whereBetweenTime('Completed', $s, $d)
                    ->select();

                $tj_arr[$k]['Company_Name'] = $v['Company_Name'];

                if(!empty($tj_date)){

                    $tj_arr[$k]['Page'][$b[$i]] = intval($tj_date[0]['Pages']);
                    $tj_arr[$k]['cword'][$b[$i]] = intval($tj_c_o[0]['word']);
                    $tj_arr[$k]['oword'][$b[$i]] = intval($tj_o_c[0]['word']);

                }else{

                    $tj_arr[$k]['Page'][$b[$i]] = 0;
                    $tj_arr[$k]['cword'][$b[$i]] = 0;
                    $tj_arr[$k]['oword'][$b[$i]] = 0;
                }

                $tj_arr[$k]['j_total'][$b[$i]] = $tj_arr[$k]['cword'][$b[$i]] + $tj_arr[$k]['oword'][$b[$i]];
            }

        }

//        dump($js_arr);
//        dump($tj_arr);die;


        $arr = [];

        // 组装 数据 // YTD 计算 （取数组 前几个月的值 求和 计算）
        foreach ($js_arr as $v => $j){

            foreach ($tj_arr as $vo => $t){

                if($js_arr[$v]['Company_Name'] == $tj_arr[$vo]['Company_Name']){

                    $arr[$v]['Company_Name'] = $j['Company_Name'];

                    $arr[$v]['js_Page'] = $js_arr[$v]['Page'];
                    $arr[$v]['tj_Page'] = $tj_arr[$vo]['Page'];

                    $arr[$v]['js_cword'] = $js_arr[$v]['cword'];
                    $arr[$v]['tj_cword'] = $tj_arr[$vo]['cword'];

                    $arr[$v]['js_oword'] = $js_arr[$v]['oword'];
                    $arr[$v]['tj_oword'] = $tj_arr[$vo]['oword'];

                    $arr[$v]['js_j_total'] = $js_arr[$v]['j_total'];
                    $arr[$v]['tj_j_total'] = $tj_arr[$vo]['j_total'];


                    $m_js_arr[$v] = array_slice($js_arr[$v]['Page'], 0, $mon);
                    $m_tj_arr[$v] = array_slice($tj_arr[$vo]['Page'], 0, $mon);

                    $arr[$v]['mon_js'] = array_sum($m_js_arr[$v]);
                    $arr[$v]['mon_tj'] = array_sum($m_tj_arr[$v]);
                }
            }
        }





        // 计算 Total
        foreach ($arr as $k => $v){

            $arr[$k]['t_js_Page'] = array_sum($arr[$k]['js_Page']);
            $arr[$k]['t_tj_Page'] = array_sum($arr[$k]['tj_Page']);

            $arr[$k]['t_js_cword'] = array_sum($arr[$k]['js_cword']);
            $arr[$k]['t_js_oword'] = array_sum($arr[$k]['js_oword']);

            $arr[$k]['t_tj_cword'] = array_sum($arr[$k]['tj_cword']);
            $arr[$k]['t_tj_oword'] = array_sum($arr[$k]['tj_oword']);

            $arr[$k]['js_total'] = $arr[$k]['t_js_cword'] + $arr[$k]['t_js_oword'];
            $arr[$k]['tj_total'] = $arr[$k]['t_tj_cword'] + $arr[$k]['t_tj_oword'];
        }
        //halt($arr);

        // 计算 YTD
        foreach ($arr as $k => $v){

            if(!empty($q_arr)){

                foreach ($q_arr as $key => $val){

                    if($v['Company_Name'] == $val['Company_Name']) {

                        $arr[$k]['q_mon_js'] = $q_arr[$key]['q_mon_js'];
                        $arr[$k]['q_mon_tj'] = $q_arr[$key]['q_mon_tj'];
                    }else{
                        $arr[$k]['q_mon_js'] = 0;
                        $arr[$k]['q_mon_tj'] = 0;
                    }
                }

            }else{
                $arr[$k]['q_mon_js'] = 0;
                $arr[$k]['q_mon_tj'] = 0;
            }

            $arr[$k]['c_mon_js'] = $arr[$k]['mon_js'] - $arr[$k]['q_mon_js'];
            $arr[$k]['c_mon_tj'] = $arr[$k]['mon_tj'] - $arr[$k]['q_mon_tj'];
        }

        //halt($arr);


        // 按格式组装 列表数据
        if(empty($arr)){
            $a['count'] = 0;
        }else{
            $a['count'] = $js_company_arr->total();
        }
        $a['code'] = 0;
        $a['msg'] = '成功';
        $a['data'] = $arr;

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['y'=>$y, 'mon'=>$mon, 'a'=>json_encode($a),
                'q_year'=>$q_year, 'f_year'=>$f_year, 's_year'=>$s_year, 'year'=>$year
            ]);
        }

        // 返回接口数据
        return json($a);
    }

}
