<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjContractReview as PjContractReviewModel;
use app\facade\PjProjectProfile as PjProjectProfileModel;
use app\facade\PjTranslationEvaluation as PjTranslationEvaluationModel;
use think\Controller;
use think\Request;
use think\Db;

// 翻译评估 控制器
class PjTranslationEvaluation extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_translation_evaluation');

        // 查询 校对人员 签名
        $sign = Admin::field('id, name, job_id, sign')
            ->where('job_id', 'in', [10,11,8,4,15,6])
            ->where('status', 0)->select();

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',12)->value('intro');
        if($request->has('search_type')){
            $data= $request->only(['search_type']);
            $search_type=$data ["search_type"];
        }
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [ 'sign' => $sign, 'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword,
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),
                'search_type'=>$search_type
            ]);
        }

        // 调用模型获取列表
        $list = PjTranslationEvaluationModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_translation_evaluation');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 查询 可供预选的 文件编码
        $file_code = PjContractReviewModel::field('Filing_Code')
            ->order('id desc')->limit(30000)->select();

        // 翻译
        $tr = Admin::field('name')->where('status', 0)
            ->where('job_id', 'in', [10,11,8,4,15,6,19])->select();

        // 直接返回视图
        return view('form-translation_evaluation', ['file_code'=>$file_code, 'tr'=>$tr]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjTranslationEvaluationModel::get($id);

        $arr1 = ['A','B','C','D'];
        $arr = [];
        foreach($arr1 as $key=>$val){
            $arr[$key]['name'] = $arr1[$key];
        }

        // 翻译
        $tr = Admin::field('name')->where('status', 0)
            ->where('job_id', 'in', [10,11,8,4,15,6,19])->select();

        return view('form-translation_evaluation-view', ['info'=>$res, 'tr'=>$tr,'arr'=>$arr]);
    }

    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        /*计算 整体评价*/
        $f = [$data['Omission'],$data['Extra_Translation'],$data['Understanding'],
            $data['Typo_or_Data_Error'], $data['Inconsistent_within_the_File'],
            $data['Inconsistent_with_Other_Translators'], $data['Mistranslation'],
            $data['Incorrect_Punctuation'], $data['Inconsistent_with_Target_Language_Features'],
            $data['Grammar_Mistakes'], $data['Sentence_Quality'],
            $data['Refer_to_the_References'], $data['Corrected_Problems_Reoccur'],
        ];

        // 对数组中的所有值进行计数
        $num = array_count_values($f);

        $n['A'] = isset($num['A']) ? $num['A'] : 0;
        $n['B'] = isset($num['B']) ? $num['B'] : 0;
        $n['C'] = isset($num['C']) ? $num['C'] : 0;
        $n['D'] = isset($num['D']) ? $num['D'] : 0;

        if($n['A'] >= 10 && $n['C'] == 0 && $n['D'] == 0){

            $data['Overall_Evaluation'] = 'A';

        }elseif ($n['A'] + $n['B'] >= 8 && $n['D'] == 0){

            $data['Overall_Evaluation'] = 'B';

        }elseif ($n['C'] + $n['D'] >= 10 && $n['D'] >= 5){

            $data['Overall_Evaluation'] = 'D';
        }else{
            $data['Overall_Evaluation'] = 'C';
        }

        // 保存
        PjTranslationEvaluationModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        /*计算 整体评价*/
        $f = [$data['Omission'],$data['Extra_Translation'],$data['Understanding'],
            $data['Typo_or_Data_Error'], $data['Inconsistent_within_the_File'],
            $data['Inconsistent_with_Other_Translators'], $data['Mistranslation'],
            $data['Incorrect_Punctuation'], $data['Inconsistent_with_Target_Language_Features'],
            $data['Grammar_Mistakes'], $data['Sentence_Quality'],
            $data['Refer_to_the_References'], $data['Corrected_Problems_Reoccur'],
        ];

        // 对数组中的所有值进行计数
        $num = array_count_values($f);

        $n['A'] = isset($num['A']) ? $num['A'] : 0;
        $n['B'] = isset($num['B']) ? $num['B'] : 0;
        $n['C'] = isset($num['C']) ? $num['C'] : 0;
        $n['D'] = isset($num['D']) ? $num['D'] : 0;

        if($n['A'] >= 10 && $n['C'] == 0 && $n['D'] == 0){

            $data['Overall_Evaluation'] = 'A';

        }elseif ($n['A'] + $n['B'] >= 8 && $n['D'] == 0){

            $data['Overall_Evaluation'] = 'B';

        }elseif ($n['C'] + $n['D'] >= 10 && $n['D'] >= 5){

            $data['Overall_Evaluation'] = 'D';
        }else{
            $data['Overall_Evaluation'] = 'C';
        }

        PjTranslationEvaluationModel::update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjTranslationEvaluationModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // (批量) 校对人员 签名
    public function sign($id, $sign)
    {

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id',$uid)->value('job_id');

        // 检查身份信息是否匹配
        if(!in_array($job_id, [10,11,8,4,15,6])) { // 是否为该职位人员

            // 返回数据
            return json(['msg' => '职位不匹配,操作失败']);

        // 是否为 本人操作
        } else if($sign == session('administrator')['name']){

            $id_arr = explode(',', $id);

            // 更新数据
            foreach ($id_arr as $k => $v) {

                Db::name('pj_translation_evaluation')->where('id', $v)->update(['sign' => $sign]);
            }

            // 返回数据
            return json(['msg' => '操作成功']);

        }else{
            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);
        }
    }

    // 根据 文件编码 查询 拆分后的 文件名
    public function get_job_name($code)
    {

        $job_name = PjProjectProfileModel::where('Filing_Code', $code)
            ->field('Job_Name')->order('id desc')->select();

        // 返回值
        return json(['data'=>$job_name]);
    }

    // 根据 文件编码、文件名称 获取相关信息
    public function get_info($code, $name)
    {

        $info = PjProjectProfileModel::where('Filing_Code', $code)
            ->field('Translator, Company_Name, Language, Translation_Difficulty,File_Category')
            ->where('Job_Name', $name)->find();

        // 返回值
        return json(['data'=>$info]);
    }

    // 验证 文件名及人员 相同文件是否已存在
    public function check_data($code, $name)
    {
        $res = PjTranslationEvaluationModel::where(['Job_Name'=>$code, 'Translator'=>$name])->find();

        $l = session('language');

        if($l == '中文'){
            $msg = '数据已存在';
        }else{
            $msg = 'Date already exists';
        }

        if(!empty($res)){
            return json(['code'=>0, 'msg'=>$msg]);
        }else{
            return json(['code'=>1]);
        }
    }
    public function Batch_edit(Request $request)
    {

        try {
            $data=$request->param();
            $res = Db::name('pj_translation_evaluation')->wherein('id',$data['arr'])->update([$data['field']=>$data['numsss']]);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json(['code'=>9999,'error'=>$e->getMessage()]);
        }

        return json(['code'=>$res]);
    }
}