<?php

namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\Faq as FaqModel;
use app\facade\PjContractReview as PjContractReviewModel;
use app\facade\PjProjectProfile as PjProjectProfileModel;
use app\facade\PjCheck as PjCheckModel;
use app\facade\PjProjectProfileText as PjProjectProfileTextModel;
use app\facade\XtMessages as XtMsgModel;
use think\Controller;
use think\Request;
use think\Db;

// 项目抽查 控制器描述
class PjCheck extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_check');

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
                case 'update_time':
                    $colsData[$k]['hide'] = 'true';
                    break;
                case 'create_time':
                    $colsData[$k]['hide'] = 'true';
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide'] = 'true';
                    break;
                case 'status':
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
        $list = PjCheckModel::getList($search_type, $field, $keyword, $limit);


        // 返回数据
        return json(generate_layui_table_data($list));
    }



    public function sessionOut()
    {
        session('simple', null);
        $this->redirect('index');
    }

    // 参考库文件(多文本框 库文件 表)
    public function text_list(Request $request, $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_project_profile_text');

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {

            return view('', ['colsData' => json_encode($colsData), 'colsDatae' => $colsData]);
        }

        // 调用模型获取列表
        $list = PjProjectProfileTextModel::getList($field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_check');

        // 直接返回视图
        return view('', ['select_field' => $colsData]);
    }



    // 显示新建的表单页
    public function create($id)
    {

        // 查询 项目描述表信息
        $info = Db::name('pj_project_profile')->where('id',$id)->find();

        // N/A 选项
        $na = [['value' => 0, 'name' => 'N/A']];


        // 直接返回视图
        return view('form-check', [
            'info' => $info,
        ]);
    }



    //编辑视图
    public function edit($id)
    {
        // 查询 抽查信息
        $info = Db::name('pj_check')->where('id',$id)->find();

        // N/A 选项
        $na = [['value' => 0, 'name' => 'N/A']];


        // 直接返回视图
        return view('form-check-view', [
            'info' => $info,
        ]);
    }



    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();
        $id  = $data['id'];
        unset($data['id']);
        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        $save = PjCheckModel::create($data);

        //如果没有校对，字数修订率要同步到翻译人员每日进度百分比100的记录里
        if($data['Revision_Rate']){
            $xmms = Db::name('pj_project_profile')->where('id',$id)->find();
            $fanyi = $xmms['Translator'];
            $trans = explode(',',$fanyi);
            //如果被抽查人是翻译
            if(in_array($data['Bcheck_Person'],$trans)){
                if($xmms['Reviser'] == 'N/A'){
                    $up_rate = [
                        'Revision_Rate' => $data['Revision_Rate']
                    ];
                    $where = [
                        'Filing_Code' => $data['Filing_Code'],
                        'Job_Name' => $data['Job_Name'],
                        'delete_time' => 0,
                        'Percentage_Completed' => 100,
                        'Name_of_Translator_or_Reviser' => $data['Bcheck_Person']
                    ];
                    $res = Db::name('pj_daily_progress_tr_re')->where($where)->update($up_rate);
                }
            }
        }

        //将描述表状态改为已抽查
        $xmms = Db::name('pj_project_profile')->where('id',$id)->find();
        $Spot_Check = $xmms['Spot_Check'];
        if($save){
            if($data['Check_Cate'] == '翻译'){
                if($Spot_Check == '6'){
                    $up_data = [
                        'Spot_Check' => '5'
                    ];
                }else{
                    $up_data = [
                        'Spot_Check' => '7'
                    ];
                }

            }elseif($data['Check_Cate'] == '排版'){
                if($Spot_Check == '7'){
                    $up_data = [
                        'Spot_Check' => '5'
                    ];
                }else{
                    $up_data = [
                        'Spot_Check' => '6'
                    ];
                }

            }else{
                $up_data = [
                    'Spot_Check' => '已QCR'
                ];
            }
            $res = Db::name('pj_project_profile')->where('id',$id)
                ->update($up_data);
        }

        // 返回操作结果
        $this->redirect('pj_check/index');

    }



    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();
//        dump($data);die;
        PjCheckModel::update($data);

        //如果没有校对，字数修订率要同步到翻译人员每日进度百分比100的记录里
        if($data['Revision_Rate']){
            $xmms = Db::name('pj_project_profile')
                ->where('Filing_Code',$data['Filing_Code'])
                ->where('Job_Name',$data['Job_Name'])
                ->find();
            $fanyi = $xmms['Translator'];
            $trans = explode(',',$fanyi);
            //如果被抽查人是翻译
            if(in_array($data['Bcheck_Person'],$trans)){
                if($xmms['Reviser'] == 'N/A'){
                    $up_rate = [
                        'Revision_Rate' => $data['Revision_Rate']
                    ];
                    $where = [
                        'Filing_Code' => $data['Filing_Code'],
                        'Job_Name' => $data['Job_Name'],
                        'delete_time' => 0,
                        'Percentage_Completed' => 100,
                        'Name_of_Translator_or_Reviser' => $data['Bcheck_Person']
                    ];
                    $res = Db::name('pj_daily_progress_tr_re')->where($where)->update($up_rate);
                }
            }
        }


        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }



    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjCheckModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }



    // 数据读取 表单视图
    public function data_read($id)
    {
        // 返回视图
        return view('', ['id' => $id]);
    }

    // xml 文件上传 读取数据
    public function file_up()
    {
        // 文件对象
        $file = request()->file('file');

        // 获取文件后缀
        $temp = explode(".", $_FILES["file"]["name"]);
        $ext = end($temp);

        // 检查文件类型
        if ($file->checkExt('xml')) {

            $info = $file->move('uploads', 'xml/' . date('Ymd') . '/' . md5(time()) . '.xml');

            $file_path = $_SERVER["DOCUMENT_ROOT"] . '/uploads/' . $info->getSaveName();

            // 读取xml
            $data = readxml($file_path);
            //halt($data);

            return json(['code' => 1, 'msg' => 'Success', 'data' => $data]);
        } else {
            return json(['code' => 0, 'msg' => 'File Type Error', 'data' => '']);
        }
    }


    // 计算总词汇数
    public function total_word(Request $request)
    {
        $data = $request->post();

        $a = explode(',', $data['s']);

        $total = array_sum($a);

        return $total;
    }

    // 项目名称 重名验证
    public function check_name($name)
    {

        // 查询信息
        $res = Db::name('pj_project_profile_text')->field('id')
            ->where('Project_Name', $name)->find();

        $l = session('language');

        if ($l == '中文') {
            $msg = '项目名称已存在，请勿重名';
        } else {
            $msg = 'The Project_Name already exists';
        }

        if (!empty($res)) {
            return json(['code' => 0, 'msg' => $msg]);
        } else {
            return json(['code' => 1]);
        }
    }


    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 合同编码 获取相关信息
        $info = Db::name('pj_contract_review')
            ->where('Filing_Code', $code)->find();

        $fc_arr = explode(',', $info['File_Category']);

        $tr_arr = explode(',', $info['Translator']);
        $re_arr = explode(',', $info['Reviser']);
        $yp_arr = explode(',', $info['Pre_Formatter']);
        $hp_arr = explode(',', $info['Post_Formatter']);

        // N/A 选项
        $na = [['value' => 0, 'name' => 'N/A']];

        // 文件分类
        $document_type = dict(6);
        $document_type = array_merge($document_type, $na);
        foreach ($document_type as $k => $v) {
            if (in_array($v['name'], $fc_arr)) {
                $document_type[$k]['selected'] = true;
            }
        }

        // 翻译
        $tr = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10, 11, 12, 13, 8, 4, 15, 6, 19])
            ->where(['status' => 0, 'delete_time' => 0])->select();
        $tr = array_merge($tr, $na);
        foreach ($tr as $k => $v) {
            if (in_array($v['name'], $tr_arr)) {
                $tr[$k]['selected'] = true;
            }
        }

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10, 11, 12, 13, 8, 4, 15, 6])
            ->where(['status' => 0, 'delete_time' => 0])->select();
        $re = array_merge($re, $na);
        foreach ($re as $k => $v) {
            if (in_array($v['name'], $re_arr)) {
                $re[$k]['selected'] = true;
            }
        }

        // 预排
        $yp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19, 10, 11, 12, 13, 5])
            ->where(['status' => 0, 'delete_time' => 0])->select();
        $yp = array_merge($yp, $na);
        foreach ($yp as $k => $v) {
            if (in_array($v['name'], $yp_arr)) {
                $yp[$k]['selected'] = true;
            }
        }

        // 后排
        $hp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19, 10, 11, 12, 13, 5])
            ->where(['status' => 0, 'delete_time' => 0])->select();
        $hp = array_merge($hp, $na);
        foreach ($hp as $k => $v) {
            if (in_array($v['name'], $hp_arr)) {
                $hp[$k]['selected'] = true;
            }
        }

        // 返回值
        return json([
            'data' => $info, 'fc' => json_encode($document_type),
            'tr' => json_encode($tr), 're' => json_encode($re), 'yp' => json_encode($yp), 'hp' => json_encode($hp)
        ]);
    }

    //批量修改
    public function Batch_edit(Request $request)
    {


        try {
            $data = $request->param();
            $field = array_filter(explode(',', $data['field']));
            $numsss = array_filter(explode(',', $data['numsss']));
            $arr = [];
            foreach ($field as $k => $v) {
                foreach ($numsss as $k1 => $v1) {
                    if ($k == $k1) {
                        $arr[$v] = $v1;
                    }
                }
            }
            $res = Db::name('pj_project_profile')->wherein('id', $data['arr'])->update($arr);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json(['code' => 9999, 'error' => $e->getMessage()]);
        }

        return json(['code' => $res]);
    }

    //文件名编号效验
    public function inspection(Request $request)
    {
        try {
            $data = $request->param();
            $res = Db::name('pj_project_profile')->wherein('Filing_Code', $data['Filing_Code'])->count();
            if ($res < 1) {
                return json(['code' => 6666,]);
            }
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json(['code' => 9999, 'error' => $e->getMessage()]);
        }

        return json(['code' => $res]);
    }

    //基本信息批量修改
    public function Batch_edite(Request $request)
    {
        try {
            $data = $request->param();
            $field = array_filter(explode(',', $data['field']));
            $numsss = array_filter(explode(',', $data['numsss']));
            $arr = [];
            foreach ($field as $k => $v) {
                foreach ($numsss as $k1 => $v1) {
                    if ($k == $k1) {
                        $arr[$v] = $v1;
                    }
                }
            }
            $res = Db::name('pj_project_profile_text')->wherein('id', $data['arr'])->update($arr);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json(['code' => 9999, 'error' => $e->getMessage()]);
        }

        return json(['code' => $res]);

    }

    public function import()
    {

        try {
            require '../extend/PHPExcel/PHPExcel.php';

            $file = request()->file('file');

            if ($file) {
                $info = $file->validate(['size' => 10485760, 'ext' => 'xls,xlsx,'])->move('public/' . 'excel');
                if (!$info) {
                    $this->error('上传文件格式不正确');
                } else {
                    //获取上传到后台的文件名
                    $fileName = $info->getSaveName();
                    //获取文件路径
                    $filePath = 'public/' . 'excel/' . $fileName;
                    //获取文件后缀
                    $suffix = $info->getExtension();
                    // 判断哪种类型
                    if ($suffix == "xlsx") {
                        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
                    } else {
                        $reader = \PHPExcel_IOFactory::createReader('Excel5');
                    }
                }
                $excel = $reader->load("$filePath", $encode = 'utf-8');

                $sheet = $excel->getSheet(0);    // 读取第一个工作表(编号从 0 开始)

                $highestRow = $sheet->getHighestRow();            // 取得总行数
                $highestColumn = $sheet->getHighestColumn();    // 取得总列数
                $arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                // 一次读取一列
                $res_arr = array();
                $highestRow = round(($highestRow-18)/13);
                for ($row = 1; $row <= $highestRow; $row++) {
                    $a = ($row-1)*13+3+16;
                    $d = ($row-1)*13+15+16;
                    $f1 = ($row-1)*13+4+16;
                    $f2 = ($row-1)*13+5+16;
                    $f3 = ($row-1)*13+6+16;
                    $f4 = ($row-1)*13+7+16;
                    $f5 = ($row-1)*13+8+16;
                    $res_arr[$row - 1]['name'] = trim($sheet->getCell("A" . $a)->getValue());
                    $res_arr[$row - 1]['Source_Text_Word_Count'] = trim($sheet->getCell("D" . $d)->getValue());
                    $f1 = trim($sheet->getCell("F" . $f1)->getValue());
                    $f2 = trim($sheet->getCell("F" . $f2)->getValue());
                    $f3 = trim($sheet->getCell("F" . $f3)->getValue());
                    $f4 = trim($sheet->getCell("F" . $f4)->getValue());
                    $f5 = trim($sheet->getCell("F" . $f5)->getValue());
                    $res_arr[$row - 1]['One_Hundred_Percent_Repeated'] = floatval($f1)+floatval($f2)+floatval($f3)+floatval($f4);

                    $res_arr[$row - 1]['Ninety_Five_to_Ninety_Nine_Percent_Repeated'] = $f5;
                    $res_arr[$row - 1]['Total_Repetition_Rate'] = floatval($f1)+floatval($f2)+floatval($f3)+floatval($f4)+floatval($f5);
                    $res_arr[$row - 1]['Actual_Source_Text_Count'] = round($res_arr[$row - 1]['Source_Text_Word_Count']*(1-$res_arr[$row - 1]['Total_Repetition_Rate']));

                }

                foreach($res_arr as $key=>$val){
                    $up_data = [
                        'Source_Text_Word_Count' => $val['Source_Text_Word_Count'],
                        'One_Hundred_Percent_Repeated' => $val['One_Hundred_Percent_Repeated']*100,
                        'Ninety_Five_to_Ninety_Nine_Percent_Repeated' => $val['Ninety_Five_to_Ninety_Nine_Percent_Repeated']*100,
                        'Total_Repetition_Rate' => $val['Total_Repetition_Rate']*100,
                        'Actual_Source_Text_Count' =>$val['Actual_Source_Text_Count'],
                    ];
                    Db::name('pj_project_profile')->where('Job_Name',$val['name'])->update($up_data);
                }

            }

        } catch (\Exception $e) {
            $this->error('执行错误', $e->getMessage());
        }
        return json(['code' => 1, 'msg' => '导入成功']);

    }




    public function import_back()
    {

        try {
            require '../extend/PHPExcel/PHPExcel.php';

            $file = request()->file('file');
            if ($file) {
                $info = $file->validate(['size' => 10485760, 'ext' => 'xls,xlsx,'])->move('public/' . 'excel');
                if (!$info) {
                    $this->error('上传文件格式不正确');
                } else {
                    //获取上传到后台的文件名
                    $fileName = $info->getSaveName();
                    //获取文件路径
                    $filePath = 'public/' . 'excel/' . $fileName;
                    //获取文件后缀
                    $suffix = $info->getExtension();
                    // 判断哪种类型
                    if ($suffix == "xlsx") {
                        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
                    } else {
                        $reader = \PHPExcel_IOFactory::createReader('Excel5');
                    }
                }
                $excel = $reader->load("$filePath", $encode = 'utf-8');

                $sheet = $excel->getSheet(0);    // 读取第一个工作表(编号从 0 开始)

                $highestRow = $sheet->getHighestRow();            // 取得总行数
                $highestColumn = $sheet->getHighestColumn();    // 取得总列数
                $arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                // 一次读取一列
                $res_arr = array();
//                $row_arr = array();
//                for ($column = 0; $arr[$column] != 'V'; $column++) {
//                    $val = $sheet->getCellByColumnAndRow($column, 2)->getValue();
//                    $row_arr[] = $val;
//                }
//
//                $res_arr[] = $row_arr;
//                                dump($res_arr);die;
                for ($row = 2; $row <= $highestRow; $row++) {
                    $res_arr[$row - 2]['Filing_Code'] = trim($sheet->getCell("A" . $row)->getValue());
                    $res_arr[$row - 2]['Job_Name'] = trim($sheet->getCell("B" . $row)->getValue());
                    $res_arr[$row - 2]['Source_Text_Word_Count'] = trim($sheet->getCell("C" . $row)->getValue());
                    $res_arr[$row - 2]['One_Hundred_Percent_Repeated'] = trim($sheet->getCell("D" . $row)->getValue());
                    $res_arr[$row - 2]['Ninety_Five_to_Ninety_Nine_Percent_Repeated'] = trim($sheet->getCell("E" . $row)->getValue());

                }

                foreach($res_arr as $key=>$val){
                    $up_data = [
                        'Source_Text_Word_Count' => $val['Source_Text_Word_Count'],
                        'One_Hundred_Percent_Repeated' => $val['One_Hundred_Percent_Repeated'],
                        'Ninety_Five_to_Ninety_Nine_Percent_Repeated' => $val['Ninety_Five_to_Ninety_Nine_Percent_Repeated'],
                    ];
                    Db::name('pj_project_profile')->where('Filing_Code',$val['Filing_Code'])->update($up_data);
                }

                //Db::name('pj_contract_review')->insertAll($res_arr);

            }

        } catch (\Exception $e) {
            $this->error('执行错误', $e->getMessage());
        }
        return json(['code' => 1, 'msg' => '导入成功']);

    }

    //根据文件编号 自动计算项目汇总时间
    public function edit_time($id){

        $name = session('administrator')['name'];
        // 查询信息
        $res = PjProjectProfileModel::get($id);
        //halt($res);
        /*$sum = Db::name('pj_project_profile')
            ->where('Filing_Code',$res['Filing_Code'])
            ->sum('Actual_Source_Text_Count');*/
        $xmhz = Db::name('pj_contract_review')
            ->where('Filing_Code',$res['Filing_Code'])
            ->where('delete_time',0)
            ->find();

        //获取公式数值
        $num = Db::name('formula')->where('Filled_by',$name)->find();
        if(!$num['tr_number']){
            $num['tr_number'] = 1;
        }
        if(!$num['re_number']){
            $num['re_number'] = 1;
        }

        $tr_add = $num['tr_number']*3600;
        $re_add = $num['re_number']*3600;


        $tr_start = $xmhz['Translation_Start_Time'];
        $re_start = $xmhz['Revision_Start_Time'];


        if($tr_start){
            //开始时间当天零点时间戳
            $today = strtotime($tr_start);

            $today = strtotime(date("Y-m-d",$today));

            //当天晚上八点半时间戳
            $bdb = $today+20.5*3600;

            $tr_end_time = strtotime($tr_start)+$tr_add;

            if($tr_end_time < $bdb){
                $tr_end_time = date('Y-m-d H:i',$tr_end_time);
            }else{
                $cha = $tr_end_time-$bdb;
                $n = floor($cha/12/3600);

                if($n == 0){
                    $tr_end_time = $tr_end_time+12*3600;
                }else{
                    $tr_end_time = $tr_end_time+($n+1)*12*3600;
                }
                $tr_end_time = date('Y-m-d H:i',$tr_end_time);
            }


        }else{
            $tr_end_time = '';
        }

        if($re_start){
            //开始时间当天零点时间戳
            $today2 = strtotime($re_start);

            $today2 = strtotime(date("Y-m-d",$today2));

            //当天晚上八点半时间戳
            $bdb2 = $today2+20.5*3600;
            $re_end_time = strtotime($re_start)+$re_add;

            if($re_end_time < $bdb2){
                $re_end_time = date('Y-m-d H:i',$re_end_time);
            }else{
                $cha = $re_end_time-$bdb2;
                $n = floor($cha/12/3600);
                if($n == 0){
                    $re_end_time = $re_end_time+12*3600;
                }else{
                    $re_end_time = $re_end_time+($n+1)*12*3600;
                }
                $re_end_time = date('Y-m-d H:i',$re_end_time);
            }

        }else{
            $re_end_time = '';
        }

        $up_data = [
            'Translation_Delivery_Time' => $tr_end_time,
            'Revision_Delivery_Time' => $re_end_time,
        ];

        $res2 = Db::name('pj_contract_review')
            ->where('Filing_Code',$res['Filing_Code'])
            ->update($up_data);
        //同步项目描述的交付时间
        Db::name('pj_project_profile')
            ->where('Filing_Code',$res['Filing_Code'])
            ->update($up_data);

        if($res2){
            // 返回数据
            return json(['msg' => '操作成功']);
        }else{
            // 返回数据
            return json(['msg' => '操作失败']);
        }
    }


    public function pg_time($id){
        $name = session('administrator')['name'];

        $id_arr = explode(',', $id);

        $id_arr = array_reverse($id_arr);

        foreach($id_arr as $key=>$val){
            // 查询信息
            $res = PjProjectProfileModel::get($val);
            //halt($res);
            /*$sum = Db::name('pj_project_profile')
                ->where('Filing_Code',$res['Filing_Code'])
                ->sum('Actual_Source_Text_Count');*/
            $xmhz = Db::name('pj_contract_review')
                ->where('Filing_Code',$res['Filing_Code'])
                ->where('delete_time',0)
                ->find();

            //获取公式数值
            $num = Db::name('formula')->where('Filled_by',$name)->find();
            if(!$num['tr_number']){
                $num['tr_number'] = 1;
            }
            if(!$num['re_number']){
                $num['re_number'] = 1;
            }

            $tr_add = $num['tr_number']*3600;
            $re_add = $num['re_number']*3600;


            $tr_start = $xmhz['Translation_Start_Time'];
            $re_start = $xmhz['Revision_Start_Time'];


            if($tr_start){
                //开始时间当天零点时间戳
                $today = strtotime($tr_start);

                $today = strtotime(date("Y-m-d",$today));

                //当天晚上八点半时间戳
                $bdb = $today+20.5*3600;
                $tr_end_time = strtotime($tr_start)+$tr_add;

                if($tr_end_time < $bdb){
                    $tr_end_time = date('Y-m-d H:i',$tr_end_time);
                }else{
                    $cha = $tr_end_time-$bdb;
                    $n = floor($cha/12/3600);

                    if($n == 0){
                        $tr_end_time = $tr_end_time+12*3600;
                    }else{
                        $tr_end_time = $tr_end_time+($n+1)*12*3600;
                    }
                    $tr_end_time = date('Y-m-d H:i',$tr_end_time);
                }


            }else{
                $tr_end_time = '';
            }

            if($re_start){
                //开始时间当天零点时间戳
                $today2 = strtotime($re_start);

                $today2 = strtotime(date("Y-m-d",$today2));

                //当天晚上八点半时间戳
                $bdb2 = $today2+20.5*3600;
                $re_end_time = strtotime($re_start)+$re_add;

                if($re_end_time < $bdb2){
                    $re_end_time = date('Y-m-d H:i',$re_end_time);
                }else{
                    $cha = $re_end_time-$bdb2;
                    $n = floor($cha/12/3600);
                    if($n == 0){
                        $re_end_time = $re_end_time+12*3600;
                    }else{
                        $re_end_time = $re_end_time+($n+1)*12*3600;
                    }
                    $re_end_time = date('Y-m-d H:i',$re_end_time);
                }

            }else{
                $re_end_time = '';
            }

            $up_data = [
                'Translation_Delivery_Time' => $tr_end_time,
                'Revision_Delivery_Time' => $re_end_time,
            ];

            Db::name('pj_contract_review')
                ->where('Filing_Code',$res['Filing_Code'])
                ->update($up_data);

            //同步项目描述的交付时间
            Db::name('pj_project_profile')
                ->where('Filing_Code',$res['Filing_Code'])
                ->update($up_data);
        }
        //返回结果
        return json(['msg' => '操作成功']);
    }


    public function formula(){
        $name = session('administrator')['name'];
        $res = Db::name('formula')->where('Filled_by',$name)->find();
        // 直接返回视图
        return view('formula', [
            'info' => $res,
        ]);
    }

    public function form_edit(Request $request){
        // 获取提交的数据
        $data = $request->post();
        $name = session('administrator')['name'];

        $res =  Db::name('formula')->where('Filled_by',$name)->find();
        if($res){
            $data['update_time'] = time();
            Db::name('formula')->where('Filled_by',$name)->update($data);
        }else{
            $data['create_time'] = time();
            $data['Filled_by'] = $name;
            $res = Db::name('formula')->insert($data);
        }

        // 返回数据
        return json(['msg' => '设置成功']);
    }

    public function spot_check($id){

        $up_data = [
            'Spot_Check' => '待抽查',
        ];
        $res = Db::name('pj_project_profile')->where('id',$id)
            ->update($up_data);
        if($res){
            return json(['msg' => '操作成功']);
        }else{
            return json(['msg' => '执行失败']);
        }

    }
}