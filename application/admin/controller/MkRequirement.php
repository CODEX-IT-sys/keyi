<?php

namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\MkContract as MkContractModel;
use app\facade\MkRequirement as MkRequirementModel;
use think\Controller;
use think\Request;
use think\Db;

// 客户要求 控制器
class MkRequirement extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50,$simple = 1)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_requirement');

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id', 19)->value('intro');


        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {

            return view('', [
                'select_field' => $colsData, 'colsData' => json_encode($colsData),
                'intro' => $intro, 'field' => $field, 'keyword' => $keyword,'simple'=>$simple
            ]);
        }

        // 调用模型获取列表
        $list = MkRequirementModel::getList($search_type, $field, $keyword, $limit,$simple);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    //项目管理板块 显示列表
    public function index2(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50,$simple = 1)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_requirement');

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id', 19)->value('intro');


        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {

            return view('index2', [
                'select_field' => $colsData, 'colsData' => json_encode($colsData),
                'intro' => $intro, 'field' => $field, 'keyword' => $keyword,'simple'=>$simple
            ]);
        }

        // 调用模型获取列表
        $list = MkRequirementModel::getList2($search_type, $field, $keyword, $limit,$simple);

        // 返回数据
        return json(generate_layui_table_data($list));
    }


    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_requirement');

        // 直接返回视图
        return view('', ['select_field' => $colsData]);
    }

    // 显示新建的表单页
    public function create()
    {

        // 查询 可供预选的 编号值
        $contract_code = MKContractModel::field('Contract_Number')
            ->order('id desc')->select();

        // 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();

        // 直接返回视图
        return view('form-Customer', ['contract_code' => $contract_code, 'gs' => $gs]);
    }


    // 查看
    public function read($id)
    {

        // 查询信息
        $res = MkRequirementModel::get($id);





        // 直接返回视图
        return view('form-read', ['info' => $res]);
    }

    //编辑视图
    public function edit($id)
    {

        // 查询信息
        $res = MkRequirementModel::get($id);

        return view('form-Customer-view',
            ['info' => $res]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();


        // 保存
        MkRequirementModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();



        MkRequirementModel::update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 单条删除
    public function delete($id)
    {
        // 调用模型删除
        MkRequirementModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 批量删除
    public function batch_delete(Request $request, $id)
    {
        // 栏目名
        $controller = $request->controller();

        $name = session('administrator')['name'];

        // 根据栏目 查询 读写权限
        $rw = Db::name('xt_rw_auth')
            ->where('name', $name)->where('C', $controller)
            ->value('delete');

        if (empty($rw)) {

            $this->error('无权操作');

        } else if ($rw == 0) {

            $this->error('无权操作');
        }

        $id_arr = explode(',', $id);

        // 调用模型删除
        foreach ($id_arr as $k => $v) {
            MkRequirementModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 合同编码 获取相关信息
        $info = MKContractModel::where('Contract_Number', $code)
            ->field('Sales, Attention, Department, Company_Full_Name, Company_Name, Company_Code, Company_Address, 
            Remarks, Subject_Company, Subject_Company_VAT_ID, Subject_Company_Address, Subject_Company_Bank_Info')
            ->find();

        // 返回值
        return json(['data' => $info]);
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
                //var_dump($res_arr);die;
                for ($row = 2; $row <= $highestRow; $row++) {
                    $res_arr[$row - 2]['Date'] = trim($sheet->getCell("A" . $row)->getValue());
                    $res_arr[$row - 2]['Customer_Company'] = trim($sheet->getCell("B" . $row)->getValue());
                    $res_arr[$row - 2]['Attention'] = trim($sheet->getCell("C" . $row)->getValue());
                    $res_arr[$row - 2]['Department'] = trim($sheet->getCell("D" . $row)->getValue());
                    $res_arr[$row - 2]['SA'] = trim($sheet->getCell("E" . $row)->getValue());
                    $res_arr[$row - 2]['PTL'] = trim($sheet->getCell("F" . $row)->getValue());
                    $res_arr[$row - 2]['Formatting'] = trim($sheet->getCell("G" . $row)->getValue());
                    $res_arr[$row - 2]['Translation_Scope'] = trim($sheet->getCell("H" . $row)->getValue());
                    $res_arr[$row - 2]['Language_Style'] = trim($sheet->getCell("I" . $row)->getValue());
                    $res_arr[$row - 2]['Terminology'] = trim($sheet->getCell("J" . $row)->getValue());
                    $res_arr[$row - 2]['Deliverables'] = trim($sheet->getCell("K" . $row)->getValue());
                    $res_arr[$row - 2]['Glossary'] = trim($sheet->getCell("L" . $row)->getValue());

                }

                Db::name('mk_requirement')->insertAll($res_arr);

            }

        } catch (\Exception $e) {
            $this->error('执行错误', $e->getMessage());
        }
        return json(['code' => 1, 'msg' => '导入成功']);

    }
}