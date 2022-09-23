<?php

namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjTermRecord as PjTermRecordModel;
use think\Controller;
use think\Request;
use think\Db;

// 术语库记录 控制器
class PjTermRecord extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_term_record');

        foreach ($colsData as $k => $v) {
            switch ($v['Field']) {

                case 'Project_Name':
                    $colsData[$k]['width'] = 250;
                    break;
                case 'Responsible':
                    $colsData[$k]['width'] = 100;
                    $colsData[$k]['sort'] = 'true';
                    break;


                case 'remark':
                    $colsData[$k]['width'] = 180;
                    break;
                case 'create_time':
                    $colsData[$k]['hide'] = 'true';
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide'] = 'true';
                    break;
                default:
                    $colsData[$k]['width'] = 180;

            }

        }

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id', 20)->value('intro');
        $edit=[
            [
                'Field'=>'Whether_Submited',
                'Comment'=>'是否已提交项目临时库'
            ],
            [
                'Field'=>'Whether_Confirmed',
                'Comment'=>'是否已确认项目临时术语库符合填写规范'
            ],
            [
                'Field'=>'confirm_date',
                'Comment'=>'确认日期'
            ],
            [
                'Field'=>'Whether_Terms_Approved',
                'Comment'=>'是否核定项目临时数据库所有术语'
            ],

            [
                'Field'=>'Whether_Checked',

                'Comment'=>'是否已查重'
            ],
            [
                'Field'=>'Remark',
                'Comment'=>'备注'
            ],

        ];

        if ($request->has('search_type')) {
            $data = $request->only(['search_type']);
            $search_type = $data ["search_type"];
        }
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field' => $colsData, 'colsData' => json_encode($colsData),
                'intro' => $intro, 'field' => $field, 'keyword' => $keyword,
                'search_type' => $search_type,'editor'=>$edit,
            ]);
        }

        // 调用模型获取列表
        $list = PjTermRecordModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }





    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_term_record');

        // 直接返回视图
        return view('', ['select_field' => $colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 项目名称
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('delete_time',0)->order('id desc')->select();

        // 直接返回视图
        return view('form-term', [
            'text_list' => $text_list,
        ]);
    }


    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjTermRecordModel::get($id);

        // 项目名称
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('delete_time',0)->order('id desc')->select();

        return view('form-term-view', [
            'info'=>$res,'text_list'=>$text_list
        ]);
    }

    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();


        // 创建时间
        $data['create_time'] = time();
        //填表人
        $name = session('administrator')['name'];
        $data['Filled_by'] = $name;

        // 保存
        PjTermRecordModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        //更新
        PjTermRecordModel::update($data);

        //同步产品名称到项目描述表的涉及产品
        if($data['Product_Name']){
            $where = [
                'Project_Name' => $data['Project_Name'],
                'delete_time' => 0,
            ];
            $updata = [
                'Product_Involved'=>$data['Product_Name']
            ];
            Db::name('pj_project_profile')->where($where)->update($updata);
        }

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjTermRecordModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
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
        if ($job_id != 8) {

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        } else {  // 改变数据状态

            foreach ($id_arr as $k => $v) {

                Db::name('pj_contract_review')->where('id', $v)
                    ->update(['Approval_Project_Manager' => 'Yes']);
            }

            // 返回数据
            return json(['msg' => '操作成功']);
        }
    }

    // 总经理 批量确认
    public function batch_gm($id)
    {
        $id_arr = explode(',', $id);

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id', $uid)->value('job_id');

        // 检查身份信息是否匹配
        if ($job_id != 9) {

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        } else {  // 改变数据状态

            foreach ($id_arr as $k => $v) {

                Db::name('pj_contract_review')->where('id', $v)
                    ->update(['Approval_General_Manager' => 'Yes']);
            }

            // 返回数据
            return json(['msg' => '操作成功']);
        }
    }


    // 项目经理 确认
    public function project_manager($id)
    {
        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id', $uid)->value('job_id');

        // 检查身份信息是否匹配
        if ($job_id != 8) {

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        } else {// 改变数据状态
            Db::name('pj_contract_review')->where('id', $id)
                ->update(['Approval_Project_Manager' => 'Yes']);

            // 返回数据
            return json(['msg' => '操作成功']);
        }
    }

    // 总经理 确认
    public function general_manager($id)
    {
        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id', $uid)->value('job_id');

        // 检查身份信息是否匹配
        if ($job_id != 9) {

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        } else {// 改变数据状态
            Db::name('pj_contract_review')->where('id', $id)
                ->update(['Approval_General_Manager' => 'Yes']);

            // 返回数据
            return json(['msg' => '操作成功']);
        }
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

    public function Batch_edit(Request $request)
    {

        // 启动事务
        Db::startTrans();

        try {
            $data = $request->param();
            $field=array_filter(explode(',',$data['field']));
            $numsss=array_filter(explode(',',$data['numsss']));
            $arr = [];
            foreach ($field as $k => $v) {
                foreach ($numsss as $k1 => $v1) {
                    if ($k == $k1) {
                        $arr[$v] = $v1;
                    }
                }

            }

            if (isset($arr['Completed'])) {
                $arr['Completed'] = (int)$arr['Completed'];
            }
            $res = Db::name('pj_term_record')->wherein('id', $data['arr'])->update($arr);

            // 提交事务
            Db::commit();
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
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
                    $res_arr[$row - 2]['Company_Name'] = trim($sheet->getCell("C" . $row)->getValue());
                    $res_arr[$row - 2]['Pages'] = trim($sheet->getCell("D" . $row)->getValue());
                    $res_arr[$row - 2]['Source_Text_Word_Count'] = trim($sheet->getCell("E" . $row)->getValue());
                    $res_arr[$row - 2]['File_Type'] = trim($sheet->getCell("F" . $row)->getValue());
                    $res_arr[$row - 2]['Service'] = trim($sheet->getCell("G" . $row)->getValue());
                    $res_arr[$row - 2]['File_Category'] = trim($sheet->getCell("H" . $row)->getValue());
                    $res_arr[$row - 2]['Language'] = trim($sheet->getCell("I" . $row)->getValue());
                    $res_arr[$row - 2]['Format_Difficulty'] = trim($sheet->getCell("J" . $row)->getValue());
                    $res_arr[$row - 2]['Translation_Difficulty'] = trim($sheet->getCell("K" . $row)->getValue());
                    $res_arr[$row - 2]['Translator'] = trim($sheet->getCell("L" . $row)->getValue());
                    $res_arr[$row - 2]['Translation_Start_Time'] = trim($sheet->getCell("M" . $row)->getValue());
                    $res_arr[$row - 2]['Translation_Delivery_Time'] = trim($sheet->getCell("N" . $row)->getValue());
                    $res_arr[$row - 2]['Reviser'] = trim($sheet->getCell("O" . $row)->getValue());

                    /*if($sheet->getCell("O".$row)->getValue()==''){
                        $res_arr[$row-2]['Delivery_Date_Expected']  ='';
                    }else{
                        $res_arr[$row-2]['Delivery_Date_Expected']  =date('Y-m-d H:i',strtotime(gmdate('Y-m-d H:i',\PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("O".$row)->getValue()))));
                    }*/
                    $res_arr[$row - 2]['Revision_Start_Time'] = trim($sheet->getCell("P" . $row)->getValue());
                    $res_arr[$row - 2]['Revision_Delivery_Time'] = trim($sheet->getCell("Q" . $row)->getValue());
                    $res_arr[$row - 2]['Pre_Formatter'] = trim($sheet->getCell("R" . $row)->getValue());
                    $res_arr[$row - 2]['Pre_Format_Delivery_Time'] = trim($sheet->getCell("S" . $row)->getValue());
                    $res_arr[$row - 2]['Post_Formatter'] = trim($sheet->getCell("T" . $row)->getValue());
                    $res_arr[$row - 2]['Post_Format_Delivery_Time'] = trim($sheet->getCell("U" . $row)->getValue());

                    if ($sheet->getCell("V" . $row)->getValue() == '') {
                        $res_arr[$row - 2]['Delivery_Date_Expected'] = '';
                    } else {
                        $res_arr[$row - 2]['Delivery_Date_Expected'] = date('Y-m-d H:i', strtotime(gmdate('Y-m-d H:i', \PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("V" . $row)->getValue()))));
                    }
                    $res_arr[$row - 2]['Completed'] = trim($sheet->getCell("W" . $row)->getValue());
                    $res_arr[$row - 2]['Delivered_or_Not'] = trim($sheet->getCell("X" . $row)->getValue());
                    $res_arr[$row - 2]['Attention'] = trim($sheet->getCell("Y" . $row)->getValue());
                    $res_arr[$row - 2]['Customer_Requirements'] = trim($sheet->getCell("Z" . $row)->getValue());
                    $res_arr[$row - 2]['External_Reference_File'] = trim($sheet->getCell("AA" . $row)->getValue());
                    $res_arr[$row - 2]['First_Cooperation'] = trim($sheet->getCell("AB" . $row)->getValue());
                    $res_arr[$row - 2]['Quality_Requirements'] = trim($sheet->getCell("AC" . $row)->getValue());
                    $res_arr[$row - 2]['PA'] = trim($sheet->getCell("AD" . $row)->getValue());
                    $res_arr[$row - 2]['PM'] = trim($sheet->getCell("AE" . $row)->getValue());
                    $res_arr[$row - 2]['Sales'] = trim($sheet->getCell("AF" . $row)->getValue());
                    $res_arr[$row - 2]['Approval_Project_Manager'] = trim($sheet->getCell("AG" . $row)->getValue());
                    $res_arr[$row - 2]['Approval_General_Manager'] = trim($sheet->getCell("AH" . $row)->getValue());
                    $res_arr[$row - 2]['Filled_by'] = trim($sheet->getCell("AI" . $row)->getValue());
                    $res_arr[$row - 2]['Date'] = trim($sheet->getCell("AJ" . $row)->getValue());
                    $res_arr[$row - 2]['Comment'] = trim($sheet->getCell("AK" . $row)->getValue());

                }

                Db::name('pj_contract_review')->insertAll($res_arr);

            }

        } catch (\Exception $e) {
            $this->error('执行错误', $e->getMessage());
        }
        return json(['code' => 1, 'msg' => '导入成功']);

    }




    //项目汇总批量拆分成多条项目描述
    public function split($c_id)
    {
        // 文件库
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('Filled_by', session('administrator')['name'])
            ->where('delete_time', 0)->order('id desc')->select();
        // 返回视图
        return view('', ['c_id' => $c_id, 'text_list' => $text_list]);
    }

    public function add_split(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();
        // 用户
        $name = session('administrator')['name'];
        //通过id查询项目汇总表信息
        $xmhz = Db::name('pj_contract_review')->where('id', $data['c_id'])->find();
        $len = $data['split_num'];
        if ($len >= 1) {
            for ($i = 0; $i < $len; $i++) {
                $in_data = [
                    'Filing_Code' => $xmhz['Filing_Code'],
                    'Job_Name' => $xmhz['Job_Name'],
                    'Project_Name' => $data['Project_Name'],
                    'Company_Name' => $xmhz['Company_Name'],
                    'Language' => $xmhz['Language'],
                    'File_Type' => $xmhz['File_Type'],
                    'File_Category' => $xmhz['File_Category'],
                    'Translation_Delivery_Time' => $xmhz['Translation_Delivery_Time'],
                    'Revision_Delivery_Time' => $xmhz['Revision_Delivery_Time'],
                    'Pre_Format_Delivery_Time' => $xmhz['Pre_Format_Delivery_Time'],
                    'Post_Format_Delivery_Time' => $xmhz['Post_Format_Delivery_Time'],
                    'Format_Difficulty' => $xmhz['Format_Difficulty'],
                    'Translation_Difficulty' => $xmhz['Translation_Difficulty'],
                    'Pre_Formatter' => $xmhz['Pre_Formatter'],
                    'Translator' => $xmhz['Translator'],
                    'Reviser' => $xmhz['Reviser'],
                    'Post_Formatter' => $xmhz['Post_Formatter'],
                    'PA' => $xmhz['PA'],
                    'trre_range' => $xmhz['trre_range'],
                    'lang_style' => $xmhz['lang_style'],
                    'format' => $xmhz['format'],
                    'deliverables' => $xmhz['deliverables'],
                    'other_remark' => $xmhz['other_remark'],
                    'Filled_by' => $name,
                ];
                Db::name('pj_project_profile')->insert($in_data);
            }

            // 返回操作结果
            return json(['msg' => '拆分成功']);
        }

    }


    //批量添加项目描述
    public function batch_ms($id)
    {
        $id_arr = explode(',', $id);

        $id_arr = array_reverse($id_arr);
        // 用户
        $name = session('administrator')['name'];
        foreach ($id_arr as $key => $v) {
            //通过id查询项目汇总表信息
            $xmhz = Db::name('pj_contract_review')->where('id', $v)->find();
            $in_data = [
                'Filing_Code' => $xmhz['Filing_Code'],
                'Job_Name' => $xmhz['Job_Name'],
                'Company_Name' => $xmhz['Company_Name'],
                'Pages' => $xmhz['Pages'],
                'Source_Text_Word_Count' => $xmhz['Source_Text_Word_Count'],
                'Language' => $xmhz['Language'],
                'File_Type' => $xmhz['File_Type'],
                'File_Category' => $xmhz['File_Category'],
                'Translation_Delivery_Time' => $xmhz['Translation_Delivery_Time'],
                'Revision_Delivery_Time' => $xmhz['Revision_Delivery_Time'],
                'Pre_Format_Delivery_Time' => $xmhz['Pre_Format_Delivery_Time'],
                'Post_Format_Delivery_Time' => $xmhz['Post_Format_Delivery_Time'],
                'Format_Difficulty' => $xmhz['Format_Difficulty'],
                'Translation_Difficulty' => $xmhz['Translation_Difficulty'],
                'Pre_Formatter' => $xmhz['Pre_Formatter'],
                'Translator' => $xmhz['Translator'],
                'Reviser' => $xmhz['Reviser'],
                'Post_Formatter' => $xmhz['Post_Formatter'],
                'PA' => $xmhz['PA'],
                'trre_range' => $xmhz['trre_range'],
                'lang_style' => $xmhz['lang_style'],
                'format' => $xmhz['format'],
                'deliverables' => $xmhz['deliverables'],
                'other_remark' => $xmhz['other_remark'],
                'Filled_by' => $name,
            ];
            Db::name('pj_project_profile')->insert($in_data);
        }
        // 返回数据
        return json(['msg' => '操作成功']);
    }

}