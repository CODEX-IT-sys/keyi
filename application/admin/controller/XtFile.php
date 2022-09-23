<?php
namespace app\admin\controller;

use app\facade\Admin as AdminModel;
use app\facade\XtFile as XtFileModel;
use app\facade\PjProjectProfile as PjProjectProfileModel;
use app\facade\PjProjectProfileText as PjProjectProfileTextModel;
use think\Controller;
use think\Request;
use think\Db;
use think\Env;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PHPExcel;
use PHPExcel_IOFactory;

// 文件管理 控制器
class XtFile extends Controller
{
    // 项目列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {

        /*限定人员 显示项目*/
        $name = session('administrator')['name'];

        // 用户职位
        $job_id = session('administrator')['job_id'];

        // 查询器对象 判断管理层
        if(in_array($job_id, [1,6,8,9,20,23]) || $name == '张攀' || $name == '闻心宇' || $name == '周美玲' || $name == '李晓艺' ) {

            // 可见所有项目
            $list = Db::name('pj_project_profile_text')
                ->field('Project_Name, Project_Responsible')
                ->where($field, 'like', "%$keyword%")
                ->where('delete_time', 0)
                ->order('id desc')
                ->paginate($limit);

        }else{

            // 项目描述 项目列表（限定仅为参与项目的人员可见）
            $p_list = PjProjectProfileModel::getFileList();

            $p_arr = [];
            if(!empty($p_list)){ // 不为空时数组去重
                foreach ($p_list as $v){
                    $p_arr[] = $v['Project_Name'];
                }
                $p_arr = array_unique($p_arr);
            }

            // 可见的项目列表
            $list = Db::name('pj_project_profile_text')
                ->field('Project_Name, Project_Responsible')
                ->where($field, 'like', "%$keyword%")
                ->where('Project_Name', 'in', $p_arr)
                ->where('delete_time', 0)
                ->order('id desc')
                ->paginate($limit);

        }

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('',[
                'field' => $field, 'keyword' => $keyword,
            ]);
        }

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 文件列表
    public function file_list(Request $request, $Project_Name = '', $field = '', $keyword = '', $limit = 10)
    {

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户 角色信息(项目资料共用不应限制)


        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', ['Project_Name'=>$Project_Name]);
        }

        // 查询文件 列表
        $list = XtFileModel::getList($Project_Name, $field , $keyword , $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 显示新建的表单页
    public function create($Project_Name)
    {
        $file_name =  Db::name('xt_dict')->where('c_id',36)->order('sort','asc')->select();
        return view('', ['Project_Name'=>$Project_Name,'file_name'=>$file_name]);
    }

    // 显示编辑的表单页
    public function edit($id)
    {

        $info = XtFileModel::get($id);

        return view('', ['info'=>$info]);
    }

    // 验证文件夹 是否存在 不存在就创建
    public function file_dir($file_dir)
    {
        $name = session('administrator')['name'];

        $user_dir = $_SERVER["DOCUMENT_ROOT"]. '/uploads/' . $name;

        // 默认 给用户创建 同名根目录文件夹
        if(!is_dir($user_dir)) {
            // 创建 用户根文件目录
            mkdir($user_dir);
        }

        // 新建的 文件夹
        $dir = $_SERVER["DOCUMENT_ROOT"]. '/uploads/' . $name . '/' . $file_dir;

        if(!is_dir($dir)){
            // 创建 文件目录
            mkdir($dir);
        }

        return json(['code'=>0, 'msg'=>'success']);
    }

    //通过文件类型获取文件夹名称
    public function getName(){
        $type = input('fileType');

        //文件名称数据组装
        $cszl = ['翻译文件（部分已排版）','翻译文件（全部已排版）','参考文件_客户参考','参考文件_内部参考','客户要求'];
        $fywc = ['翻译修订完毕-需后排','资料收集-翻译','翻译完毕-需后排'];
        $jdwc = ['校对完毕-需后排','资料收集-校对'];
        $QA = ['QA自检报告-翻译','QA自检报告-校对'];
        $hpwc = ['后排完毕','后排完毕-需QCR'];
        $dgwj = ['定稿完毕','更库项目文件'];
        $fyQCR = ['翻译QCR完毕'];
        $pbQCR = ['排版QCR完毕'];
        $sy = ['术语'];
        $xd = ['修订完毕-需后排'];
        $arr = [];
        $arr['1'] = $cszl;
        $arr['4'] = $fywc;
        $arr['5'] = $jdwc;
        $arr['11'] = $QA;
        $arr['6'] = $hpwc;
        $arr['7'] = $dgwj;
        $arr['9'] = $pbQCR;
        $arr['10'] = $fyQCR;
        $arr['12'] = $sy;
        $arr['13'] = $sy;
        $arr['14'] = $xd;
        $data = $arr[$type];
        $list = [
            'code' => 1,
            'msg' => 'success',
            'data' => $data,
        ];
        return $list;
    }

    // 文件上传 （保留原文件名）
    public function file_up($file_dir = '')
    {

        $name = session('administrator')['name'];

        $user_dir = $_SERVER["DOCUMENT_ROOT"]. '/uploads/' . $name;

        // 默认 给用户创建 同名根目录文件夹
        if(!is_dir($user_dir)) {
            // 创建 用户根文件目录
            mkdir($user_dir);
        }

        // 新建的 文件夹
        $dir = $_SERVER["DOCUMENT_ROOT"]. '/uploads/' . $name . '/' . $file_dir;

        if(!is_dir($dir)){
            // 创建 文件目录
            mkdir($dir);
        }

        // 文件对象
        $file = request()->file('file');
        //halt($file);

        // 获取文件后缀（不限类型）
        /*$temp = explode(".", $_FILES["file"]["name"]);
        $ext = end($temp);

        // 限制文件类型
        $File_Type = ["mp3","wav","aac","gif","jpeg","jpg","png","txt",
            "zip","rar","7z","pdf","doc","docx","xls","xlsx","csv","xml"];

        // 类型判断
        if(!in_array($ext,$File_Type)){

            if(session('language') == '中文'){
                $msg = '上传失败，文件类型不合法';
            }else{
                $msg = 'Upload failed. File type is not valid';
            }

            return json(['code' => 1, 'msg' => $msg]);
        }*/

        if ($file) {

            $info = $file->move('uploads', $name .'/'. $file_dir .'/'. time() .'_'. $_FILES["file"]["name"]);

            if ($info) {
                return json(['code' => 0, 'data' => '/uploads/' . $info->getSaveName(), 'msg' => '上传成功']);
            } else {
                return json(['code' => 1, 'data' =>'', 'msg' => $file->getError()]);
            }
        } else {
            return json(['code' => 1, 'data' =>'', 'msg' => '上传失败，请稍后再试']);
        }
    }

    // 保存
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        $f_data['file_dir'] = $data['file_dir'];
        $f_data['type'] = $data['type'];
        $f_data['Project_Name'] = $data['Project_Name'];
        // 创建者
        $f_data['creator'] = session('administrator')['name'];

        $name_arr = explode(',', $data['file_name']);

        $path_arr = explode(',', $data['file_path']);

        // 解析数据 多文件遍历
        foreach ($name_arr as $k => $v){

            foreach ($path_arr as $key => $val){

                if($k == $key){

                    $f_data['file_name'] = $v;
                    $f_data['name'] = $v;
                    $f_data['path'] = $val;

                    XtFileModel::create($f_data);
                }
            }
        }

        // 返回操作结果
        return json(['msg' => '操作成功']);
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 创建者
        $data['creator'] = session('administrator')['name'];

        XtFileModel::update($data);

        // 返回操作结果
        return json(['msg' => '更新成功']);
    }

    // 单条删除(软删 or 真删)
    public function delete($id)
    {

        // 查询文件信息
        $res = Db::name('xt_file')->where('id', $id)->find();

        $path = $_SERVER["DOCUMENT_ROOT"].$res['path'];

        // 文件路径 文件删除
        if(file_exists($path)) {
            unlink($path);
        }

        // 调用模型删除
        XtFileModel::destroy($id, true);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 文本框 图片上传 （保留原文件名）
    public function img_up()
    {

        $name = session('administrator')['name'];

        $user_dir = $_SERVER["DOCUMENT_ROOT"]. '/uploads/img/' . $name;

        // 默认 给用户创建 同名 目录文件夹
        if(!is_dir($user_dir)) {
            // 创建 用户根文件目录
            mkdir($user_dir);
        }

        // 文件对象
        $file = request()->file('file');
        //halt($file);

        // 获取文件后缀（不限类型）
        $temp = explode(".", $_FILES["file"]["name"]);
        $ext = end($temp);

        // 限制文件类型
        $File_Type = ["gif","jpeg","jpg","png"];

        // 类型判断
        if(!in_array($ext,$File_Type)){

            if(session('language') == '中文'){
                $msg = '上传失败，文件类型不合法';
            }else{
                $msg = 'Upload failed. File type is not valid';
            }

            return json(['code' => 1, 'msg' => $msg]);
        }

        if ($file) {

            $info = $file->move('uploads/img/', $name .'/'. time() .'_'. $_FILES["file"]["name"]);

            if ($info) {

                return json([
                    'code' => 0,
                    'msg' => '上传成功',
                    'data' => ['src'=>'/uploads/img/' . $info->getSaveName()]
                ]);

            } else {
                return json(['code' => 1, 'data' =>'', 'msg' => $file->getError()]);
            }
        } else {
            return json(['code' => 1, 'data' =>'', 'msg' => '上传失败，请稍后再试']);
        }
    }

    // 批量下载
    public function download($id)
    {

        $id_arr = explode(',' , $id);

        foreach ($id_arr as $k => $v){

            $file_info = Db::name('xt_file')->where('id', $v)
                ->field('file_name, path')->find();

            //$download =  new \think\response\Download();

            $file_path = $_SERVER["DOCUMENT_ROOT"]. $file_info['path'];

            // 助手函数
            return download($file_path);
        }

    }

    public function upExcel(Request $request)
    {
        if ($request->isPost()) {

            set_time_limit(0);//这个很关键
            //获取导入文件路径
            $file = $_FILES['file']['tmp_name'];
            //获取Excel后缀  xls / xlsx /csv
            $inputFileType = IOFactory::identify($file);
            //获取Excel文件
            $reader = IOFactory::createReader($inputFileType);

            $spreadsheet = $reader->load($file);
            //获取工作表格
            $sheet = $spreadsheet->getActiveSheet();
            //获取列
            $highestColumn = $sheet->getHighestColumn();
            //获取总行数
            $highestRow = $sheet->getHighestRow();
            //获取总列数
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            //从第二行开始循环, 因为第一行一般都是标题~
            for ($i = 2; $i <= $highestRow; $i++) {

                $id = $sheet->getCellByColumnAndRow(1, $i)->getValue();
                $file_name = $sheet->getCellByColumnAndRow(2, $i)->getValue();
                $file_dir = $sheet->getCellByColumnAndRow(3, $i)->getValue();
                $path = $sheet->getCellByColumnAndRow(4, $i)->getValue();
                $type = $sheet->getCellByColumnAndRow(5, $i)->getValue();
                $Project_Name = $sheet->getCellByColumnAndRow(6, $i)->getValue();
                $creator = $sheet->getCellByColumnAndRow(7, $i)->getValue();
                $create_time = $sheet->getCellByColumnAndRow(8, $i)->getValue();
                $update_time = $sheet->getCellByColumnAndRow(9, $i)->getValue();
                $delete_time = $sheet->getCellByColumnAndRow(10, $i)->getValue();

                $data[] = [
                    'id'        => $id,
                    'file_name' => $file_name,
                    'file_dir'  => $file_dir,
                    'path'      => $path,
                    'type'      => $type,
                    'Project_Name' => $Project_Name,
                    'creator'     => $creator,
                    'create_time' => $create_time,
                    'update_time' => $update_time,
                    'delete_time' => $delete_time
                ];

            }
            $res = Db::table('xt_file')->insertAll($data);

            if ($res > 0) {
                return '导入成功';
            } else {
                return '导入失败';
            }

        } else {
            return '请求类型出错~';
        }
    }

}
