<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\Task as TaskModel;
use think\Controller;
use think\Request;
use think\Db;

class Task extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    public function index(Request $request , $search_type = '', $field = '', $keyword = '', $limit = 50){
        // 数据库表字段集
        $colsData = getAllField('ky_task');

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');


        // 调用模型获取基本信息
        $base = TaskModel::getOne('admin');

        //每天要完成多少页
        $data=request()->param('month');
        if(isset($data)){
            session('month',$data);

        }
        $data = session('month');

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

            $list=  Db::table('ky_pj_contract_review')->whereBetweenTime('Completed',$firstTime,$lastTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
                ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();
        }else{
            //第一行从当前时间开始
            $time= time();
            $firstTime = strtotime(date("Y-m-d"),time());

            $firstTime=intval(date("Ymd",$firstTime));

            $list=  Db::table('ky_pj_contract_review')->where('Completed','>=',$firstTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
                ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();

        }

        //var_dump($list);die;

        foreach($list as $key=>$val){
            $sub_date = $val['Completed'];
            $n = date('N', strtotime($sub_date));

            if($n == 6){
                //判断前一天是否是周五，是的话将提交页数提交到周五 不是的话虚拟一个周五
                if(array_key_exists($key-1,$list)){
                    $cha = $list[$key]['Completed'] - $list[$key-1]['Completed'];
                    if($cha == 1){
                        $list[$key-1]['sumpage'] = $list[$key-1]['sumpage']+$val['sumpage'];
                        unset($list[$key]);
                    }else{
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed']-1,
                            'sumpage' => $val['sumpage'],
                        ];
                        unset($list[$key]);
                        $list[$key] = $five;
                    }



                }

            }


            if($n == 7){
                //判断前一天是否是周五，是的话将提交页数提交到周五 不是的话虚拟一个周五
                if(array_key_exists($key-1,$list)) {
                    $cha = $list[$key]['Completed'] - $list[$key - 1]['Completed'];
                    //差值为2是周五 为1是周六，不做修改
                    if ($cha == 2) {
                        $list[$key - 1]['sumpage'] = $list[$key - 1]['sumpage'] + $val['sumpage'];
                    } else {
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed'] - 2,
                            'sumpage' => $val['sumpage'],
                        ];
                        $list[] = $five;
                    }
                }elseif(array_key_exists($key-2,$list)){
                    $cha2 = $list[$key]['Completed'] - $list[$key-2]['Completed'];

                    //差值为2是周五
                    if($cha2 == 2){
                        $list[$key-2]['sumpage'] = $list[$key-2]['sumpage']+$val['sumpage'];
                    }else{
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed']-2,
                            'sumpage' => $val['sumpage'],
                        ];
                        $list[] = $five;
                    }

                }else{
                    //新建一个周五的数据
                    $five = [
                        'Completed' => $val['Completed']-2,
                        'sumpage' => $val['sumpage'],
                    ];
                    $list[] = $five;
                }
                unset($list[$key]);

            }


        }
        //var_dump($list);die;
        $Completed = array_column($list,'Completed');
        array_multisort($Completed,SORT_ASC,$list);
        //var_dump($list);die;

        $preDate = '';
        $totalRow = [];

        //合计数据
        $tpre = 0;
        $ttrs = 0;
        $trev = 0;
        $tpos = 0;
        $sumpage = 0;
        //平均值
        $tr_gpa = [];
        $tr_gpe = [];
        $pre_gpa = [];
        $pre_gpe = [];
        $post_gpa = [];
        $post_gpe = [];


        foreach($list as $key=>$val){
            if(empty($preDate)){
                $list[$key]['work_days'] = 1;
            }else{
                $nowDate = $val['Completed'];
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

                $list[$key]['work_days'] = $days-$weekday+$reduce;
            }
            //计算完成后 将新的循环日期赋值给临时变量
            $preDate = $val['Completed'];

            $work_days = $list[$key]['work_days'];
            $list[$key]['pre_formatter'] = $base['pre_formatter'];
            $list[$key]['pre_page'] = $base['pre_page'];
            $list[$key]['translator'] = $base['translator'];
            $list[$key]['trs_page'] = $base['trs_page'];
            $list[$key]['revisor'] = $base['revisor'];
            $list[$key]['rev_page'] = $base['rev_page'];
            $list[$key]['post_formatter'] = $base['post_formatter'];
            $list[$key]['post_page'] = $base['post_page'];

            $pre_total = $base['pre_formatter']*$base['pre_page']*$work_days;
            $list[$key]['pre_total']= $pre_total;
            $trs_total = $base['translator']*$base['trs_page']*$work_days;
            $list[$key]['trs_total']= $trs_total;
            $rev_total = $base['revisor']*$base['rev_page']*$work_days;
            $list[$key]['rev_total']= $rev_total;
            $post_total = $base['post_formatter']*$base['post_page']*$work_days;
            $list[$key]['post_total']= $post_total;

            $list[$key]['trre_GapPages'] = $val['sumpage']-$list[$key]['trs_total']-$list[$key]['rev_total'];
            $list[$key]['trre_GapPeople'] = number_format($list[$key]['trre_GapPages']/25, 2);
            $list[$key]['pre_GapPages'] = $val['sumpage']-$list[$key]['pre_total'];
            $list[$key]['pre_GapPeople'] = number_format($list[$key]['pre_GapPages']/$base['pre_page'],2);;
            $list[$key]['post_GapPages'] = $val['sumpage']-$list[$key]['post_total'];
            $list[$key]['post_GapPeople'] = number_format($list[$key]['post_GapPages']/$base['post_page'],2);

            //合计行数据
            $totalRow['pre_formatter'] = $base['pre_formatter'];
            $totalRow['pre_page'] = $base['pre_page'];
            $totalRow['translator'] = $base['translator'];
            $totalRow['trs_page'] = $base['trs_page'];
            $totalRow['revisor'] = $base['revisor'];
            $totalRow['rev_page'] = $base['rev_page'];
            $totalRow['post_formatter'] = $base['post_formatter'];
            $totalRow['post_page'] = $base['post_page'];

            $tpre += $pre_total;
            $ttrs += $trs_total;
            $trev += $rev_total;
            $tpos += $post_total;
            $sumpage += $val['sumpage'];

            $tr_gpa[] = $list[$key]['trre_GapPages'];
            $tr_gpe[] = $list[$key]['trre_GapPeople'];
            $pre_gpa[] = $list[$key]['pre_GapPages'];
            $pre_gpe[] = $list[$key]['pre_GapPeople'];
            $post_gpa[] = $list[$key]['post_GapPages'];
            $post_gpe[] = $list[$key]['post_GapPeople'];



        }
        $totalRow['pre_total'] = $tpre;
        $totalRow['trs_total'] = $ttrs;
        $totalRow['rev_total'] = $trev;
        $totalRow['post_total'] = $tpos;
        $totalRow['sumpage'] = $sumpage;

        $totalRow['trre_GapPages'] = number_format(array_sum($tr_gpa)/count($tr_gpa),2);
        $totalRow['trre_GapPeople'] = number_format(array_sum($tr_gpe)/count($tr_gpe),2);
        $totalRow['pre_GapPages'] = number_format(array_sum($pre_gpa)/count($pre_gpa),2);
        $totalRow['pre_GapPeople'] = number_format(array_sum($pre_gpe)/count($pre_gpe),2);
        $totalRow['post_GapPages'] = number_format(array_sum($post_gpa)/count($post_gpa),2);
        $totalRow['post_GapPeople'] = number_format(array_sum($post_gpe)/count($post_gpe),2);

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

    //分表1 PM01所有
    public function index2(Request $request){
        // 数据库表字段集
        $colsData = getAllField('ky_task');

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');


        // 调用模型获取基本信息
        $base = TaskModel::getOne('PM01');

        //每天要完成多少页
        $data=request()->param('month2');
        if(isset($data)){
            session('month2',$data);

        }
        $data = session('month2');

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

            $list=  Db::table('ky_pj_contract_review')->where('PM','PM01')->whereBetweenTime('Completed',$firstTime,$lastTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
                ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();
        }else{
            //第一行从当前时间开始
            $time= time();
            $firstTime = strtotime(date("Y-m-d"),time());

            $firstTime=intval(date("Ymd",$firstTime));

            $list=  Db::table('ky_pj_contract_review')->where('PM','PM01')->where('Completed','>=',$firstTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
                ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();

        }

        foreach($list as $key=>$val){
            $sub_date = $val['Completed'];
            $n = date('N', strtotime($sub_date));

            if($n == 6){
                //判断前一天是否是周五，是的话将提交页数提交到周五 不是的话虚拟一个周五
                if(array_key_exists($key-1,$list)){
                    $cha = $list[$key]['Completed'] - $list[$key-1]['Completed'];
                    if($cha == 1){
                        $list[$key-1]['sumpage'] = $list[$key-1]['sumpage']+$val['sumpage'];
                        unset($list[$key]);
                    }else{
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed']-1,
                            'sumpage' => $val['sumpage'],
                        ];
                        unset($list[$key]);
                        $list[$key] = $five;
                    }



                }

            }


            if($n == 7){
                //判断前一天是否是周五，是的话将提交页数提交到周五 不是的话虚拟一个周五
                if(array_key_exists($key-1,$list)) {
                    $cha = $list[$key]['Completed'] - $list[$key - 1]['Completed'];
                    //差值为2是周五 为1是周六，不做修改
                    if ($cha == 2) {
                        $list[$key - 1]['sumpage'] = $list[$key - 1]['sumpage'] + $val['sumpage'];
                    } else {
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed'] - 2,
                            'sumpage' => $val['sumpage'],
                        ];
                        $list[] = $five;
                    }
                }elseif(array_key_exists($key-2,$list)){
                    $cha2 = $list[$key]['Completed'] - $list[$key-2]['Completed'];

                    //差值为2是周五
                    if($cha2 == 2){
                        $list[$key-2]['sumpage'] = $list[$key-2]['sumpage']+$val['sumpage'];
                    }else{
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed']-2,
                            'sumpage' => $val['sumpage'],
                        ];
                        $list[] = $five;
                    }

                }else{
                    //新建一个周五的数据
                    $five = [
                        'Completed' => $val['Completed']-2,
                        'sumpage' => $val['sumpage'],
                    ];
                    $list[] = $five;
                }
                unset($list[$key]);

            }


        }

        $Completed = array_column($list,'Completed');
        array_multisort($Completed,SORT_ASC,$list);

        //var_dump($list);

        $preDate = '';
        $totalRow = [];

        //合计数据
        $tpre = 0;
        $ttrs = 0;
        $trev = 0;
        $tpos = 0;
        $sumpage = 0;

        $tr_gpa = [];
        $tr_gpe = [];
        $pre_gpa = [];
        $pre_gpe = [];
        $post_gpa = [];
        $post_gpe = [];


        foreach($list as $key=>$val){
            if(empty($preDate)){
                $list[$key]['work_days'] = 1;
            }else{
                $nowDate = $val['Completed'];
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

                $list[$key]['work_days'] = $days-$weekday+$reduce;
            }
            //计算完成后 将新的循环日期赋值给临时变量
            $preDate = $val['Completed'];

            $work_days = $list[$key]['work_days'];
            $list[$key]['pre_formatter'] = $base['pre_formatter'];
            $list[$key]['pre_page'] = $base['pre_page'];
            $list[$key]['translator'] = $base['translator'];
            $list[$key]['trs_page'] = $base['trs_page'];
            $list[$key]['revisor'] = $base['revisor'];
            $list[$key]['rev_page'] = $base['rev_page'];
            $list[$key]['post_formatter'] = $base['post_formatter'];
            $list[$key]['post_page'] = $base['post_page'];

            $pre_total = $base['pre_formatter']*$base['pre_page']*$work_days;
            $list[$key]['pre_total']= $pre_total;
            $trs_total = $base['translator']*$base['trs_page']*$work_days;
            $list[$key]['trs_total']= $trs_total;
            $rev_total = $base['revisor']*$base['rev_page']*$work_days;
            $list[$key]['rev_total']= $rev_total;
            $post_total = $base['post_formatter']*$base['post_page']*$work_days;
            $list[$key]['post_total']= $post_total;

            $list[$key]['trre_GapPages'] = $val['sumpage']-$list[$key]['trs_total']-$list[$key]['rev_total'];
            $list[$key]['trre_GapPeople'] = number_format($list[$key]['trre_GapPages']/25, 2);
            $list[$key]['pre_GapPages'] = $val['sumpage']-$list[$key]['pre_total'];
            $list[$key]['pre_GapPeople'] = number_format($list[$key]['pre_GapPages']/$base['pre_page'],2);;
            $list[$key]['post_GapPages'] = $val['sumpage']-$list[$key]['post_total'];
            $list[$key]['post_GapPeople'] = number_format($list[$key]['post_GapPages']/$base['post_page'],2);

            //合计行数据
            $totalRow['pre_formatter'] = $base['pre_formatter'];
            $totalRow['pre_page'] = $base['pre_page'];
            $totalRow['translator'] = $base['translator'];
            $totalRow['trs_page'] = $base['trs_page'];
            $totalRow['revisor'] = $base['revisor'];
            $totalRow['rev_page'] = $base['rev_page'];
            $totalRow['post_formatter'] = $base['post_formatter'];
            $totalRow['post_page'] = $base['post_page'];

            $tpre += $pre_total;
            $ttrs += $trs_total;
            $trev += $rev_total;
            $tpos += $post_total;
            $sumpage += $val['sumpage'];

            $tr_gpa[] = $list[$key]['trre_GapPages'];
            $tr_gpe[] = $list[$key]['trre_GapPeople'];
            $pre_gpa[] = $list[$key]['pre_GapPages'];
            $pre_gpe[] = $list[$key]['pre_GapPeople'];
            $post_gpa[] = $list[$key]['post_GapPages'];
            $post_gpe[] = $list[$key]['post_GapPeople'];



        }
        $totalRow['pre_total'] = $tpre;
        $totalRow['trs_total'] = $ttrs;
        $totalRow['rev_total'] = $trev;
        $totalRow['post_total'] = $tpos;
        $totalRow['sumpage'] = $sumpage;

        $totalRow['trre_GapPages'] = number_format(array_sum($tr_gpa)/count($tr_gpa),2);
        $totalRow['trre_GapPeople'] = number_format(array_sum($tr_gpe)/count($tr_gpe),2);
        $totalRow['pre_GapPages'] = number_format(array_sum($pre_gpa)/count($pre_gpa),2);
        $totalRow['pre_GapPeople'] = number_format(array_sum($pre_gpe)/count($pre_gpe),2);
        $totalRow['post_GapPages'] = number_format(array_sum($post_gpa)/count($post_gpa),2);
        $totalRow['post_GapPeople'] = number_format(array_sum($post_gpe)/count($post_gpe),2);

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

    //分表1 PM01所有
    public function index3(Request $request){
        // 数据库表字段集
        $colsData = getAllField('ky_task');

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');


        // 调用模型获取基本信息
        $base = TaskModel::getOne('PM02');

        //每天要完成多少页
        $data=request()->param('month3');
        if(isset($data)){
            session('month3',$data);

        }
        $data = session('month3');

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

            $list=  Db::table('ky_pj_contract_review')->where('PM','PM02')->whereBetweenTime('Completed',$firstTime,$lastTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
                ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();
        }else{
            //第一行从当前时间开始
            $time= time();
            $firstTime = strtotime(date("Y-m-d"),time());

            $firstTime=intval(date("Ymd",$firstTime));

            $list=  Db::table('ky_pj_contract_review')->where('PM','PM02')->where('Completed','>=',$firstTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
                ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();

        }

        foreach($list as $key=>$val){
            $sub_date = $val['Completed'];
            $n = date('N', strtotime($sub_date));

            if($n == 6){
                //判断前一天是否是周五，是的话将提交页数提交到周五 不是的话虚拟一个周五
                if(array_key_exists($key-1,$list)){
                    $cha = $list[$key]['Completed'] - $list[$key-1]['Completed'];
                    if($cha == 1){
                        $list[$key-1]['sumpage'] = $list[$key-1]['sumpage']+$val['sumpage'];
                        unset($list[$key]);
                    }else{
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed']-1,
                            'sumpage' => $val['sumpage'],
                        ];
                        unset($list[$key]);
                        $list[$key] = $five;
                    }



                }

            }


            if($n == 7){
                //判断前一天是否是周五，是的话将提交页数提交到周五 不是的话虚拟一个周五
                if(array_key_exists($key-1,$list)) {
                    $cha = $list[$key]['Completed'] - $list[$key - 1]['Completed'];
                    //差值为2是周五 为1是周六，不做修改
                    if ($cha == 2) {
                        $list[$key - 1]['sumpage'] = $list[$key - 1]['sumpage'] + $val['sumpage'];
                    } else {
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed'] - 2,
                            'sumpage' => $val['sumpage'],
                        ];
                        $list[] = $five;
                    }
                }elseif(array_key_exists($key-2,$list)){
                    $cha2 = $list[$key]['Completed'] - $list[$key-2]['Completed'];

                    //差值为2是周五
                    if($cha2 == 2){
                        $list[$key-2]['sumpage'] = $list[$key-2]['sumpage']+$val['sumpage'];
                    }else{
                        //新建一个周五的数据
                        $five = [
                            'Completed' => $val['Completed']-2,
                            'sumpage' => $val['sumpage'],
                        ];
                        $list[] = $five;
                    }

                }else{
                    //新建一个周五的数据
                    $five = [
                        'Completed' => $val['Completed']-2,
                        'sumpage' => $val['sumpage'],
                    ];
                    $list[] = $five;
                }
                unset($list[$key]);

            }


        }

        $Completed = array_column($list,'Completed');
        array_multisort($Completed,SORT_ASC,$list);
        //var_dump($list);

        $preDate = '';
        $totalRow = [];

        //合计数据
        $tpre = 0;
        $ttrs = 0;
        $trev = 0;
        $tpos = 0;
        $sumpage = 0;

        $tr_gpa = [];
        $tr_gpe = [];
        $pre_gpa = [];
        $pre_gpe = [];
        $post_gpa = [];
        $post_gpe = [];


        foreach($list as $key=>$val){
            if(empty($preDate)){
                $list[$key]['work_days'] = 1;
            }else{
                $nowDate = $val['Completed'];
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

                $list[$key]['work_days'] = $days-$weekday+$reduce;
            }
            //计算完成后 将新的循环日期赋值给临时变量
            $preDate = $val['Completed'];

            $work_days = $list[$key]['work_days'];
            $list[$key]['pre_formatter'] = $base['pre_formatter'];
            $list[$key]['pre_page'] = $base['pre_page'];
            $list[$key]['translator'] = $base['translator'];
            $list[$key]['trs_page'] = $base['trs_page'];
            $list[$key]['revisor'] = $base['revisor'];
            $list[$key]['rev_page'] = $base['rev_page'];
            $list[$key]['post_formatter'] = $base['post_formatter'];
            $list[$key]['post_page'] = $base['post_page'];

            $pre_total = $base['pre_formatter']*$base['pre_page']*$work_days;
            $list[$key]['pre_total']= $pre_total;
            $trs_total = $base['translator']*$base['trs_page']*$work_days;
            $list[$key]['trs_total']= $trs_total;
            $rev_total = $base['revisor']*$base['rev_page']*$work_days;
            $list[$key]['rev_total']= $rev_total;
            $post_total = $base['post_formatter']*$base['post_page']*$work_days;
            $list[$key]['post_total']= $post_total;

            $list[$key]['trre_GapPages'] = $val['sumpage']-$list[$key]['trs_total']-$list[$key]['rev_total'];
            $list[$key]['trre_GapPeople'] = number_format($list[$key]['trre_GapPages']/25, 2);
            $list[$key]['pre_GapPages'] = $val['sumpage']-$list[$key]['pre_total'];
            $list[$key]['pre_GapPeople'] = number_format($list[$key]['pre_GapPages']/$base['pre_page'],2);;
            $list[$key]['post_GapPages'] = $val['sumpage']-$list[$key]['post_total'];
            $list[$key]['post_GapPeople'] = number_format($list[$key]['post_GapPages']/$base['post_page'],2);

            //合计行数据
            $totalRow['pre_formatter'] = $base['pre_formatter'];
            $totalRow['pre_page'] = $base['pre_page'];
            $totalRow['translator'] = $base['translator'];
            $totalRow['trs_page'] = $base['trs_page'];
            $totalRow['revisor'] = $base['revisor'];
            $totalRow['rev_page'] = $base['rev_page'];
            $totalRow['post_formatter'] = $base['post_formatter'];
            $totalRow['post_page'] = $base['post_page'];

            $tpre += $pre_total;
            $ttrs += $trs_total;
            $trev += $rev_total;
            $tpos += $post_total;
            $sumpage += $val['sumpage'];

            $tr_gpa[] = $list[$key]['trre_GapPages'];
            $tr_gpe[] = $list[$key]['trre_GapPeople'];
            $pre_gpa[] = $list[$key]['pre_GapPages'];
            $pre_gpe[] = $list[$key]['pre_GapPeople'];
            $post_gpa[] = $list[$key]['post_GapPages'];
            $post_gpe[] = $list[$key]['post_GapPeople'];



        }
        $totalRow['pre_total'] = $tpre;
        $totalRow['trs_total'] = $ttrs;
        $totalRow['rev_total'] = $trev;
        $totalRow['post_total'] = $tpos;
        $totalRow['sumpage'] = $sumpage;

        $totalRow['trre_GapPages'] = number_format(array_sum($tr_gpa)/count($tr_gpa),2);
        $totalRow['trre_GapPeople'] = number_format(array_sum($tr_gpe)/count($tr_gpe),2);
        $totalRow['pre_GapPages'] = number_format(array_sum($pre_gpa)/count($pre_gpa),2);
        $totalRow['pre_GapPeople'] = number_format(array_sum($pre_gpe)/count($pre_gpe),2);
        $totalRow['post_GapPages'] = number_format(array_sum($post_gpa)/count($post_gpa),2);
        $totalRow['post_GapPeople'] = number_format(array_sum($post_gpe)/count($post_gpe),2);

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

    public function test(){
        $a = $this->get_weekend_days('20211021','20211023');
        var_dump($a);
    }

    //编辑视图
    public function edit(Request $request)
    {
        $data = $request->param('belong');

        // 查询信息
        $res = TaskModel::getOne($data);

        return view('form-task-view', [
            'info'=>$res
        ]);
    }

    public function update(Request $request){
        $data = $request->post();
        if(empty($data)){
            return $this->error('数据不能为空');
        }

        $res = Db::name('Task')
            ->where('id',$data['id'])
            ->update($data);

        if(!$res){
            return $this->error('修改失败');
        }
        // 返回操作结果
        echo "<script>history.go(-2);</script>";

    }



    //计算两个日期之间周末的天数
    function get_weekend_days($start_date, $end_date, $weekend_days=2) {

        $data = array();
        if (strtotime($start_date) > strtotime($end_date)) list($start_date, $end_date) = array($end_date, $start_date);

        $start_reduce = $end_add = 0;
        $start_N      = date('N',strtotime($start_date));
        $start_reduce = ($start_N == 7) ? 1 : 0;

        $end_N = date('N',strtotime($end_date));

        // 进行单、双休判断，默认按单休计算
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


    /**
     * 获取每天要提交的总页数
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    /*public function getDeliverPage(){

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

        //每天要完成多少页
        $mt=  Db::table('ky_pj_contract_review')->whereBetweenTime('Completed',$firstTime,$lastTime)->where('delete_time',0)->field('Completed,sum(Pages) as sumpage')
            ->where('Delivered_or_Not','<>','CXL')->group('Completed')->select();

        return $mt;
    }*/
}