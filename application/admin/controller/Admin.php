<?php
namespace app\admin\controller;

use app\facade\Admin as AdminModel;
use think\Controller;
use think\Request;
use think\Db;

// 用户 账号管理
class Admin extends Controller
{
    // 用户的验证器名称
    private $validate = '\app\common\validate\Admin';

    // 验证失败抛出异常
    protected $failException = true;

    // 针对控制器方法的中间件
    protected $middleware = [
        'AjaxCheck' => ['only' => ['save', 'update', 'updatePassword']],
    ];

    /**
     * 显示用户列表
     * @param Request $request
     * @param string  $keyword 搜索关键词
     * @return Json|View
     * @throws \think\exception\DbException
     */
    public function index(Request $request, $field = '', $keyword = '', $limit = 10)
    {

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('list',['field'=>$field, 'keyword'=>$keyword]);
        }

        // 调用模型获取用户列表
        $list = AdminModel::getList($field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    //计算更新翻译修订他人的校对比率

    public function gxbl21(){
        $where1 = [
            'Percentage_Completed' => 100,
            'delete_time' => 0,
        ];
        $res = Db::name('pj_daily_progress_tr_re')->where('Work_Content','in',['TR Modify Other'])
            ->where('Work_Date','>',20220831)->where($where1)->select();

        foreach($res as $key=>$val){
            if($val['Revision_Words'] > 0 && $val['Original_Chinese_Characters'] > 0){
                $rate = ($val['Revision_Words']/$val['Original_Chinese_Characters'])*100;
                $rate = number_format($rate,2);
                $up_data = [
                    'Revision_Rate' => $rate
                ];
                Db::name('pj_daily_progress_tr_re')->where('id',$val['id'])->update($up_data);
            }
        }
        echo '计算更新翻译修订他人的校对比率';
    }


    //计算更新校对比率

    public function gxbl22(){
        $where1 = [
            'Percentage_Completed' => 100,
            'delete_time' => 0,
        ];
        $res = Db::name('pj_daily_progress_tr_re')->where('Work_Content','in',['Revise','RE Modify','RE Finalize'])
            ->where('Work_Date','>',20220831)->where($where1)->select();

        foreach($res as $key=>$val){
            if($val['Revision_Words'] > 0 && $val['Original_Chinese_Characters'] > 0){
                $rate = ($val['Revision_Words']/$val['Original_Chinese_Characters'])*100;
                $rate = number_format($rate,2);
                $up_data = [
                    'Revision_Rate' => $rate
                ];
                Db::name('pj_daily_progress_tr_re')->where('id',$val['id'])->update($up_data);
            }
        }
        echo '计算更新校对比率';
    }
    //同步校对比率 翻译修订他人
    public function gxbl23(){

        $where1 = [
            'Percentage_Completed' => 100,
            'delete_time' => 0,
        ];
        $ysh = Db::name('pj_daily_progress_tr_re')->where('Work_Content','in',['TR Modify Other'])->where('Work_Date','>',20220831)->where($where1)->select();

        foreach($ysh as $key=>$val) {
            if ($val['Revision_Rate'] > 0) {
                //同步校对比率到翻译同事
                $where = [
                    'Filing_Code' => $val['Filing_Code'],
                    'Job_Name' => $val['Job_Name'],
                    'Percentage_Completed' => 100,
                    'delete_time' => 0,
                ];
                $record = Db('pj_daily_progress_tr_re')->where($where)
                    ->where('Work_Content', ['eq', 'Translate'], ['eq', 'TR Modify'], ['eq', 'TR Finalize'], 'or')->count();
                if ($record == 1) {
                    $upData = [
                        'Revision_Rate' => $val['Revision_Rate']
                    ];
                    $res = Db('pj_daily_progress_tr_re')->where($where)
                        ->where('Work_Content', ['eq', 'Translate'], ['eq', 'TR Modify'], ['eq', 'TR Finalize'], 'or')
                        ->update($upData);


                }
            }
        }
        echo '同步翻译修订他人的校对比率到翻译同事';
    }

    public function gxbl24(){
        //同步校对比率到翻译同事
        $where1 = [
            'Percentage_Completed' => 100,
            'delete_time' => 0,
        ];
        $ysh = Db::name('pj_daily_progress_tr_re')->where('Work_Content','in',['Revise','RE Modify','RE Finalize'])->where('Work_Date','>',20220831)->where($where1)->select();

        foreach($ysh as $key=>$val){
            if ($val['Revision_Rate'] > 0) {
                //同步校对比率到翻译同事
                $where = [
                    'Filing_Code' => $val['Filing_Code'],
                    'Job_Name' => $val['Job_Name'],
                    'Percentage_Completed' => 100,
                    'delete_time' => 0,
                ];

                //判断是否有翻译修订他人记录
                $xd_other = Db('pj_daily_progress_tr_re')->where($where)
                    ->where('Work_Content', 'TR Modify Other')->find();
                if ($xd_other) {
                    $upData = [
                        'Revision_Rate' => $val['Revision_Rate']
                    ];
                    $res = Db('pj_daily_progress_tr_re')->where($where)
                        ->where('Work_Content', 'TR Modify Other')
                        ->update($upData);
                } else {
                    $record = Db('pj_daily_progress_tr_re')->where($where)
                        ->where('Work_Content', ['eq', 'Translate'], ['eq', 'TR Modify'], ['eq', 'TR Finalize'], 'or')->count();
                    if ($record == 1) {
                        $upData = [
                            'Revision_Rate' => $val['Revision_Rate']
                        ];
                        $res = Db('pj_daily_progress_tr_re')->where($where)
                            ->where('Work_Content', ['eq', 'Translate'], ['eq', 'TR Modify'], ['eq', 'TR Finalize'], 'or')
                            ->update($upData);
                    }
                }
            }
        }

        echo '同步校对比率到翻译同事';
    }


    //取消QCR状态
    public function qx_qcr(){
        $list = Db::table('ky_pj_project_profile')
            ->alias('a')
            ->leftjoin('ky_pj_contract_review b','a.Filing_Code = b.Filing_Code')
            ->where('b.Date','<',20220616)
            ->where('b.Delivered_or_Not','=','Yes')
            ->field(['a.id','a.Spot_Check','b.Delivered_or_Not','b.Date'])
            ->order('a.Spot_Check desc')
            ->select();
        foreach($list as $key=>$val){
            if($val['Spot_Check'] == 9 || $val['Spot_Check'] == 8 ){
                $up = [
                    'Spot_Check' => 1
                ];

                $res  = Db::table('ky_pj_project_profile')->where('id',$val['id'])->update($up);
            }
        }
        echo '完成';
    }

    //提取所有的公司名称
    public function getCompanyName(){
        //$list = Db::name('mk_feseability')->where('delete_time',0)->field('id,Company_Name')->select();
        $list = Db::name('mk_customer')->where('delete_time',0)->field('id,Company_Name')->select();
        $data = array_column($list,'Company_Name');
        $data = array_unique($data);
        //var_dump($data);die;
        $arr = [];
        foreach($data as $key=>$val){
            if(is_cn_or_en($val) == 'allcn'){
                $arr[] = $val;
            }
        }
        var_dump($arr);die;
        //将公司名称导入到词库
        foreach($arr as $k=>$v){
            $res = Db::name('xt_dict')->where('c_id',35)->where('cn_name',$v)->find();
            if(!$res){
                $add_data = [
                    'c_id' => 35,
                    'cn_name' => $v,
                ];
                Db::name('xt_dict')->insert($add_data);
            }
        }
        return 'success';
    }

    public function ggjd(){
        $data =Db::name('pj_project_profile')->where('delete_time',0)->field(['id','Stage'])->order('id desc')->limit(1000)->select();
        foreach($data as $key=>$val){
            if($val['Stage'] == '校对（全校）'){
                $update = [
                    'Stage' => '校对'
                ];
            }elseif($val['Stage'] == '未开始'){
                $update = [
                    'Stage' => '预排'
                ];
            }else{
                $update = [];
            }
            $res = Db::table('ky_pj_project_profile')->where('id',$val['id'])->update($update);
        }
        echo '完成';
    }

    //批量更新提前交付天数
    public function jsTime(){
        $data = Db::name('pj_contract_review')->where('delete_time',0)->field(['id','Delivery_Date_Expected','Completed','Early_days'])->select();
        foreach($data as $key=>$val){
            $expect = strtotime($val["Delivery_Date_Expected"]); //客户期望日期
            $expect = strtotime(date('Ymd',$expect));

            $completed = strtotime($val["Completed"]);
            $early_days = round(($completed - $expect)/86400);
            if($early_days >100 || $early_days < -100){
                $early_days = -999;
            }
            if($early_days > 0 && $early_days < 101){
                $early_days = '*'.$early_days;
            }
            $where = [
                'delete_time'=>0,
                'id' => $val['id']
            ];
            $updata = [
                'Early_days' => $early_days
            ];
            $res = Db::table('ky_pj_contract_review')->where($where)->update($updata);
        }
    }

    //批量更新翻译评估的文件分类
    public function fypg(){
        $data = Db::name('pj_translation_evaluation')->where('delete_time',0)->field('id,Filing_Code,Job_Name,File_Category')->select();
        foreach($data as $key=>$val){
            if(!$val['File_Category']){
                $cate = Db::name('pj_project_profile')->where('Filing_Code',$val['Filing_Code'])->where('Job_Name',$val['Job_Name'])->value('File_Category');
                $up = [
                    'File_Category' => $cate,
                ];
                $res = Db::name('pj_translation_evaluation')->where('id',$val['id'])->update($up);
            }
        }
        echo 'success';
    }

    //同步项目汇总记录到项目放行
    public function tb_xmfx(){
        //查询2022年开始的项目汇总记录
        $xmhz = Db::name('pj_contract_review')
            ->where('delete_time',0)
            ->where('Date','egt','20220501')
            ->where('Delivered_or_Not','Yes')
            ->field('Filing_Code,Job_Name,Translator,Reviser,Date,Service,PA')
            ->select();

        foreach($xmhz as $key=>$val){
            if($val['Service'] == '翻译' || $val['Service'] == '校对'){
                $tr = explode(',',$val['Translator']);
                if($tr){
                    $tran = $tr['0'];
                }else{
                    $tran = '';
                }
                //判断是否存在校对，是的话检查人则为校对，否则为翻译
                $reviser = explode(',',$val['Reviser']);
                if($reviser){
                    $p = $reviser['0'];
                    if($p == 'N/A'){
                        $p = $tran;
                    }
                }else{
                    $p = $tran;
                }
            }else{
                $p = $val['PA'];
            }

            if($p == 'N/A' || $p == ''){
                $p = $val['PA'];
            }

            $res = Db::name('pj_project_release')
                ->where('Filing_Code',$val['Filing_Code'])
                ->find();
            if(!$res){
                $add_data = [
                    'Filing_Code' => $val['Filing_Code'],
                    'Job_Name' => $val['Job_Name'],
                    'Inspected_by' => $p,
                    'Translation' => 'C',
                    'Terminology' => 'C',
                    'Language_Quality' => 'C',
                    'Special_Noun' => 'C',
                    'Measurement' => 'C',
                    'Symbol' => 'C',
                    'Drawing' => 'C',
                    'Abbreviation' => 'C',
                    'Layout_and_Format' => 'C',
                    'Others' => 'C',
                    'Existing_Issue' => 'C',
                    'Correction_Result' => 'C',
                    'Reviser' => 'Yes',
                    'Filled_by' => $p,

                ];
                Db::name('pj_project_release')->insert($add_data);
            }else{
                $up_data = [
                    'Reviser' => 'Yes',
                    'Project_Manager' => 'Yes',

                ];
                Db::name('pj_project_release')->where('id',$res['id'])->update($up_data);
            }
        }
        echo 'success';
    }
    //同步项目放行中校对人员和项目经理的值为Yes
    public function tb_yes(){
        $xmfx = Db::name('pj_project_release')->where('delete_time',0)->select();
        foreach($xmfx as $key=>$val){
            $up_data = [
                'Reviser' => 'Yes',
                'Project_Manager' =>'Yes'
            ];

            $res = Db::name('pj_project_release')->where('id',$val['id'])->update($up_data);
        }
        echo '完成';
    }
    //同步字数修订率
    public function pgxd(){
        $check = Db::name('pj_check')->where('delete_time',0)->select();

        foreach($check as $key=>$val){
            if($val['Revision_Rate']){
                $xmms = Db::name('pj_project_profile')
                    ->where('Filing_Code',$val['Filing_Code'])
                    ->where('Job_Name',$val['Job_Name'])
                    ->find();
                $fanyi = $xmms['Translator'];
                $trans = explode(',',$fanyi);
                //如果被抽查人是翻译
                if(in_array($val['Bcheck_Person'],$trans)){
                    if($xmms['Reviser'] == 'N/A'){
                        $up_rate = [
                            'Revision_Rate' => $val['Revision_Rate']
                        ];
                        $where = [
                            'Filing_Code' => $val['Filing_Code'],
                            'Job_Name' => $val['Job_Name'],
                            'delete_time' => 0,
                            'Percentage_Completed' => 100,
                            'Name_of_Translator_or_Reviser' => $val['Bcheck_Person']
                        ];
                        $res = Db::name('pj_daily_progress_tr_re')->where($where)->update($up_rate);
                    }
                }
            }
        }
        echo 'hello world';
    }

    //修改实际源语数量
    public function xgActualNumber(){

        $list = Db::name('pj_daily_progress_tr_re')
            ->where('Work_Date','>=',20220501)
            ->where('delete_time',0)
            ->field(['id','Filing_Code','Job_Name','Actual_Source_Text_Count'])
            ->select();
        foreach($list as $key=>$val){
            $number = Db::name('pj_project_profile')
                ->where('Filing_Code',$val['Filing_Code'])
                ->where('Job_Name',$val['Job_Name'])
                ->value('Actual_Source_Text_Count');

            $up_data = [
                'Actual_Source_Text_Count' => $number,
            ];

            $res = Db::name('pj_daily_progress_tr_re')->where('id',$val['id'])->update($up_data);

        }

    }

    //修改QCR状态
    public function xgqcr(){
        $where = [
            'delete_time' => 0,

        ];
        $list = Db::name('pj_project_profile')
            ->where('delete_time',0)
            ->field(['id','Spot_Check'])
            ->select();
        foreach($list as $key=>$val){

            if($val['Spot_Check'] == '待翻译QCR'){
                $up_data = [
                    'Spot_Check' => 9,
                ];
            }elseif($val['Spot_Check'] == '待排版QCR'){
                $up_data = [
                    'Spot_Check' => 8,
                ];
            }elseif($val['Spot_Check'] == '翻译QCR'){
                $up_data = [
                    'Spot_Check' => 7,
                ];
            }elseif($val['Spot_Check'] == '排版QCR'){
                $up_data = [
                    'Spot_Check' => 6,
                ];
            }elseif($val['Spot_Check'] == '翻译QCR,排版QCR'){
                $up_data = [
                    'Spot_Check' => 5,
                ];
            }else{
                $up_data = [

                ];
            }


            $res = Db::name('pj_project_profile')->where('id',$val['id'])->update($up_data);

        }

    }

    //批量修改项目数据库Date
    public function gxDate(){
        $data = Db::name('pj_project_database')->where('delete_time',0)->field(['id','Filing_Code'])->select();
        foreach($data as $key=>$val){

            $date = substr($val['Filing_Code'], 2, 8);
            $where = [
                'delete_time'=>0,
                'id' => $val['id']
            ];
            $updata = [
                'Date' => $date
            ];
            $res = Db::table('ky_pj_project_database')->where($where)->update($updata);
        }
    }
    public function test(){
        $list = Db::name('mk_invoicing')->field(['id','Date_of_Balance','payment_time'])->limit(0,100)->select();
       /* foreach($list as $key=>$val){
             if(!empty($val['payment_time'])){
                 $data = [
                     'Date_of_Balance' => $val['payment_time']
                 ];
                  $res = Db::name('mk_invoicing')->where('id',$val['id'])->update($data);
             }
         }*/
        var_dump($list);
    }
    // 显示新建用户的表单页
    public function create()
    {
        // 查询部门信息
        $bm = Db::name('xt_department')->select();

        // 查询职位信息
        $job = Db::name('xt_job')->select();

        // 本职语种
        $yy = Db::name('xt_dict')->field('id as value, en_name as name')->where('c_id', 1)->select();

        // 直接返回视图
        return view('', ['bm'=>$bm, 'job'=>$job, 'yy'=>json_encode($yy)]);
    }

    // 保存新建的用户
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 数据验证
        $this->validate($data, 'app\common\validate\Admin');

        // 调用模型创建系统用户
        AdminModel::createAdmin($data);

        // 返回数据
        return json(['msg' => '创建成功']);
    }

    /**
     * 显示查看用户的表单页（不可编辑）
     * @param int $id
     * @return View
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        // 要查询的字段
        $field = ['id', 'name', 'email', 'phone', 'First_language', 'department_id', 'job_id', 'entry_time', 'dimission_time', 'sign', 'trainee', 'status'];

        // 调用模型获取用户信息
        $info = AdminModel::getOne($id, $field);

        $yy_arr = explode(',', $info['First_language']);

        // 语种
        $yy = Db::name('xt_dict')->field('id as value, en_name as name')->where('c_id', 1)->select();

        foreach ($yy as $k => $v){
            if(in_array($v['name'],$yy_arr)){
                $yy[$k]['selected'] = true;
            }
        }

        // 返回视图
        return view('', ['info'=>$info, 'yy'=>json_encode($yy)]);
    }

    /**
     * 显示编辑用户表单页
     * @param int $id
     * @return View
     * @throws \think\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
        // 要查询的字段
        $field = ['id', 'name', 'email', 'phone', 'First_language', 'department_id', 'job_id', 'entry_time', 'dimission_time', 'sign', 'trainee', 'status'];

        // 调用模型获取用户信息
        $info = AdminModel::getOne($id, $field);

        // 查询部门信息
        $bm = Db::name('xt_department')->select();

        // 查询职位信息
        $job = Db::name('xt_job')->select();

        // 语种
        $yy_arr = explode(',', $info['First_language']);

        $yy = Db::name('xt_dict')->field('id as value, en_name as name')->where('c_id', 1)->select();

        foreach ($yy as $k => $v){
            if(in_array($v['name'],$yy_arr)){
                $yy[$k]['selected'] = true;
            }
        }

        // 返回视图
        return view('', ['info' => $info, 'bm'=>$bm, 'job'=>$job, 'yy'=>json_encode($yy)]);
    }

    // 保存更新的用户
    public function update(Request $request,$id)
    {
        // 获取提交的数据
        $data = $request->put();

        // 数据验证（使用场景）
        $this->validate(array_merge($data, ['id' => $id]), $this->validate . '.' . $request->action());

        // 调用模型更新用户
        AdminModel::updateAdmin($data, $id);

        // 返回数据
        return json(['msg' => '更新成功']);
    }

    // 删除指定的用户
    public function delete($id)
    {
        // 调用模型删除用户
        AdminModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    /**
     * 显示修改密码的表单页
     * @param int $id
     * @return View
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editPassword($id)
    {
        // 调用模型，获取用户的名称
        $name = AdminModel::getFieldById($id, 'name');

        // 返回视图
        return view('', ['name' => $name, 'id'=>$id]);
    }

    // 更新密码
    public function updatePassword(Request $request,$id)
    {
        // 获取提交的数据
        $data = $request->put();

        // 数据验证（使用场景）
        $this->validate(array_merge($data, ['id' => $id]), $this->validate . '.' . $request->action());

        // 调用模型，更新密码
        AdminModel::updatePassword($id, $data['password']);

        // 返回数据
        return json(['msg' => '密码更新成功']);
    }

    // 签名（图片水印）上传
    public function up_sign()
    {
        // 文件对象
        $file = request()->file('file');

        // 获取文件后缀
        $temp = explode(".", $_FILES["file"]["name"]);
        $ext = end($temp);

        // 类型判断
        if(!in_array($ext,array("gif","jpeg","jpg","png"))){

            return json(['code' => 1, 'msg' => '上传失败，文件类型不合法']);
        }

        if ($file) {
            $info = $file->move('uploads', date('Ymd') . '/' .$_FILES["file"]["name"]);

            if ($info) {
                return json(['code' => 0, 'data' => '/uploads/' . $info->getSaveName(), 'msg' => '上传成功']);
            } else {
                return json(['code' => 1, 'data' =>'', 'msg' => $file->getError()]);
            }
        } else {
            return json(['code' => 1, 'data' =>'', 'msg' => '上传失败，请稍后再试']);
        }
    }
}