<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjContractReview as PjContractReviewModel;
use app\facade\PjProjectProfile as PjProjectProfileModel;
use app\facade\PjProjectProfileText as PjProjectProfileTextModel;
use app\facade\XtMessages as XtMsgModel;
use think\Controller;
use think\Request;
use think\Db;

// 项目描述 控制器描述
class PjProjectProfile extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_project_profile');
        $a=$colsData[0];
        $colsData[0]=$colsData[3];
        $colsData[3]=$a;
        $b=$colsData[1];
        $colsData[1]=$colsData[4];
        $colsData[4]=$b;
//        dump($colsData);
//        die;
        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'Filing_Code':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['fixed']='left';
                    $colsData[$k]['sort']='true';
                    break;
                case 'Company_Name':
                    $colsData[$k]['width']=100;
                    break;
                case 'Project_Name':
                    $colsData[$k]['width']=200;
                    break;
                case 'Job_Name':
                    $colsData[$k]['width']=140;
                    $colsData[$k]['fixed']='left';
                    break;
                case 'Pages':
                    $colsData[$k]['width']=60;
                    break;
                case 'Source_Text_Word_Count':
                    $colsData[$k]['width']=90;
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide']=true;
                    $colsData[$k]['sort']='true';
                    break;
                case 'PA':
                    $colsData[$k]['hide']=true;
                    $colsData[$k]['sort']='true';
                    break;
                case 'CODEX_Team':
//                    $colsData[$k]['width']=100;
                    $colsData[$k]['hide']=true;
                    break;
                case 'Sub_Contracted':
                    $colsData[$k]['hide']=true;
                    break;

                case 'Pre_Format_Delivery_Time':
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Translation_Delivery_Time':
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Revision_Delivery_Time':
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Post_Format_Delivery_Time':
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Final_Delivery_Time':
                    $colsData[$k]['style']='background-color:green;color:white';
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Translator':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Reviser':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Actual_Source_Text_Count':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Pre_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Post_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                default:
                    $colsData[$k]['width']=80;

            }

        }

//        dump($colsData);die;
        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',8)->value('intro');
        $edit=[
            [
                'Field'=>'Product_Involved',
                'Comment'=>'涉及产品'
            ],
            [
                'Field'=>'Project_Name',
                'Comment'=>'项目名称'
            ],
            [
                'Field'=>'File_Usage_and_Linguistic_Specification',
                'Comment'=>'文件用途和语言规范'
            ],
            [
                'Field'=>'Pre_Formatter',
                'Comment'=>'预排版人员'
            ],
            [
                'Field'=>'Product_Involved',
                'Comment'=>'涉及产品'
            ],
            [
                'Field'=>'Pre_Format_Delivery_Time',
                'Comment'=>'预排版交付时间'
            ],
            [
                'Field'=>'Translator',
                'Comment'=>'翻译人员'
            ],
            [
                'Field'=>'Translation_Delivery_Time',
                'Comment'=>'翻译交付时间'
            ],
            [
                'Field'=>'Reviser',
                'Comment'=>'校对人员'
            ],
            [
                'Field'=>'Revision_Delivery_Time',
                'Comment'=>'校对交付时间'
            ],
            [
                'Field'=>'Post_Formatter',
                'Comment'=>'后排版人员'
            ],
            [
                'Field'=>'Post_Format_Delivery_Time',
                'Comment'=>'后排版交付时间'
            ],
            [
                'Field'=>'Final_Delivery_Time',

                'Comment'=>'最终交付时间'
            ],
             [
                'Field'=>'Comment',
                'Comment'=>'备注'
            ],

        ];

        if($request->has('search_type')){
            $data= $request->only(['search_type']);
            $search_type=$data ["search_type"];
        }
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),
                'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword,'editor'=>$edit,
                'search_type'=>$search_type
            ]);
        }

        // 调用模型获取列表
        $list = PjProjectProfileModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 参考库文件(多文本框 库文件 表)
    public function text_list(Request $request, $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_project_profile_text');

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {

            return view('', ['colsData' => json_encode($colsData),'colsDatae'=>$colsData]);
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
        $colsData = getAllField('ky_pj_project_profile');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 查询 可供预选的 编号值
        $file_code = PjContractReviewModel::field('Filing_Code')
            ->order('id desc')->limit(30000)->select();

        // N/A 选项
        $na = [['value'=>0, 'name'=>'N/A']];

		// 文件类型
		$File_Type = Db::name('xt_dict')->where('c_id',4)->select();

        // 文件分类
        $document_type = dict(6);
        $document_type = array_merge($document_type, $na);
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 排版难度
		$pb = Db::name('xt_dict')->where('c_id',7)->order('sort','asc')->select();
		
		// 翻译难度
		$fy = Db::name('xt_dict')->where('c_id',8)->order('sort','asc')->select();

        // 翻译
        $tr = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6,19])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $tr = array_merge($tr, $na);

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $re = array_merge($re, $na);

        // 预排
        $yp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $yp = array_merge($yp, $na);

        // 后排
        $hp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $hp = array_merge($hp, $na);

        // 项目助理
        $pa = Admin::field('name')->where(['job_id'=> 7, 'status'=> 0])->select();

        // 文件库
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('Filled_by', session('administrator')['name'])
            ->where('delete_time',0)->order('id desc')->select();

        // 直接返回视图
        return view('form-project_profile', [
            'file_code'=>$file_code,'yy'=>$yy, 'pb'=>$pb, 'fy'=>$fy, 'File_Type'=>$File_Type,
            'document_type'=>json_encode($document_type), 'text_list'=>$text_list,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa
        ]);
    }

    // 显示新建的表单页
    public function text_create()
    {
        // N/A 选项
        $na = [['value'=>0, 'name'=>'N/A']];

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $re = array_merge($re, $na);

        // 直接返回视图
        return view('', ['re'=>json_encode($re)]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjProjectProfileModel::get($id);

        $fc_arr = explode(',', $res['File_Category']);

        $tr_arr = explode(',', $res['Translator']);
        $re_arr = explode(',', $res['Reviser']);
        $yp_arr = explode(',', $res['Pre_Formatter']);
        $hp_arr = explode(',', $res['Post_Formatter']);

        // N/A 选项
        $na = [['value'=>0, 'name'=>'N/A']];
		
		// 文件类型
		$File_Type = Db::name('xt_dict')->where('c_id',4)->select();

        // 文件分类
        $document_type = dict(6);
        $document_type = array_merge($document_type, $na);
        foreach ($document_type as $k => $v){
            if(in_array($v['name'],$fc_arr)){
                $document_type[$k]['selected'] = true;
            }
        }
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 排版难度
		$pb = Db::name('xt_dict')->where('c_id',7)->order('sort','asc')->select();
		
		// 翻译难度
		$fy = Db::name('xt_dict')->where('c_id',8)->order('sort','asc')->select();

        // 翻译
        $tr = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6,19])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $tr = array_merge($tr, $na);
        foreach ($tr as $k => $v){
            if(in_array($v['name'],$tr_arr)){
                $tr[$k]['selected'] = true;
            }
        }

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $re = array_merge($re, $na);
        foreach ($re as $k => $v){
            if(in_array($v['name'],$re_arr)){
                $re[$k]['selected'] = true;
            }
        }

        // 预排
        $yp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $yp = array_merge($yp, $na);
        foreach ($yp as $k => $v){
            if(in_array($v['name'],$yp_arr)){
                $yp[$k]['selected'] = true;
            }
        }

        // 后排
        $hp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $hp = array_merge($hp, $na);
        foreach ($hp as $k => $v){
            if(in_array($v['name'],$hp_arr)){
                $hp[$k]['selected'] = true;
            }
        }

        // 项目助理
        $pa = Admin::field('name')->where('job_id', 7)->where('status', 0)->select();

        // 文件库
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('Filled_by', session('administrator')['name'])
            ->where('delete_time',0)->order('id desc')->select();

        return view('form-project_profile-view', [
            'info'=>$res,'yy'=>$yy, 'pb'=>$pb, 'fy'=>$fy, 'File_Type'=>$File_Type,
            'document_type'=>json_encode($document_type), 'text_list'=>$text_list,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa
        ]);
    }

    //编辑视图
    public function text_edit($id)
    {
        // 查询信息
        $res = PjProjectProfileTextModel::get($id);

        $re_arr = explode(',', $res['Project_Responsible']);

        // N/A 选项
        $na = [['value'=>0, 'name'=>'N/A']];

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $re = array_merge($re, $na);
        foreach ($re as $k => $v){
            if(in_array($v['name'],$re_arr)){
                $re[$k]['selected'] = true;
            }
        }

        return view('', ['info'=>$res, 're'=>json_encode($re)]);
    }

    // 查看 信息视图
    public function view_file($id)
    {
        // 查询信息
        $res = Db::name('pj_project_profile_text')->where('Project_Name', $id)->find();

        $re_arr = explode(',', $res['Project_Responsible']);

        // N/A 选项
        $na = [['value'=>0, 'name'=>'N/A']];

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $re = array_merge($re, $na);
        foreach ($re as $k => $v){
            if(in_array($v['name'],$re_arr)){
                $re[$k]['selected'] = true;
            }
        }

        return view('text_edit', ['info'=>$res, 're'=>json_encode($re)]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        $save=  PjProjectProfileModel::create($data);
//        $save->schedule()->save(['status' => 'thinkphp']);
//         项目任务分配 提醒消息 信息(Pre、Post format; TR、RE、PA)
        $pjm_data['cn_title'] = '您有新项目信息待处理！';
        $pjm_data['en_title'] = 'You have new Project to pending';
        $pjm_data['status'] = 0;
        $pjm_data['content'] = 'Filing_Code: '. $data['Filing_Code'];

        $name = [];
        $name[0] = explode(',', $data['Translator']);
        $name[1] = explode(',', $data['Reviser']);
        $name[2] = explode(',', $data['Pre_Formatter']);
        $name[3] = explode(',', $data['Post_Formatter']);

        $name[4][0] = $data['PA'];

        // 消息提醒 群发
        foreach ($name as $key => $val){

            if(!empty($val)){

                foreach ($val as $k => $v){

                    $pjm_data['name'] = $v;

                    XtMsgModel::create($pjm_data);
                }
            }
        }

        // 返回操作结果
        $this->redirect('index');
    }

    // 新建 保存数据
    public function text_save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        PjProjectProfileTextModel::create($data);

        // 返回操作结果
        $this->redirect('text_list');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();
//        dump($data);die;
        Db::name('pj_project_database')->where('Filing_Code',$data['Filing_Code'])->update(['Product_Involved'=>$data['Product_Involved']]);
        Db::name('pj_project_profile')->where('Filing_Code',$data['Filing_Code'])->update(['Product_Involved'=>$data['Product_Involved']]);
        PjProjectProfileModel::update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 更新
    public function text_update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        PjProjectProfileTextModel::update($data);

        // 返回操作结果
        $this->redirect('text_list');
    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjProjectProfileModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 删除
    public function text_delete($id)
    {
        // 调用模型删除
        PjProjectProfileTextModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 数据读取 表单视图
    public function data_read($id)
    {
        // 返回视图
        return view('',['id' => $id]);
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
        if($file->checkExt('xml')){

            $info = $file->move('uploads', 'xml/'. date('Ymd') . '/' . md5(time()) . '.xml');

            $file_path = $_SERVER["DOCUMENT_ROOT"].'/uploads/'.$info->getSaveName();

            // 读取xml
            $data = readxml($file_path);
            //halt($data);

            return json(['code' => 1, 'msg' => 'Success', 'data' => $data]);
        }else{
            return json(['code' => 0, 'msg' => 'File Type Error', 'data' => '']);
        }
    }


    // 计算总词汇数
    public function total_word(Request $request)
    {
        $data = $request->post();

        $a = explode(',' , $data['s']);

        $total = array_sum($a);

        return $total;
    }

    // 项目名称 重名验证
    public function check_name($name)
    {

        // 查询信息
        $res = Db::name('pj_project_profile_text')->field('id')
            ->where('Project_Name',$name)->find();

        $l = session('language');

        if($l == '中文'){
            $msg = '项目名称已存在，请勿重名';
        }else{
            $msg = 'The Project_Name already exists';
        }

        if(!empty($res)){
            return json(['code'=>0, 'msg'=>$msg]);
        }else{
            return json(['code'=>1]);
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
        $na = [['value'=>0, 'name'=>'N/A']];

        // 文件分类
        $document_type = dict(6);
        $document_type = array_merge($document_type, $na);
        foreach ($document_type as $k => $v){
            if(in_array($v['name'],$fc_arr)){
                $document_type[$k]['selected'] = true;
            }
        }

        // 翻译
        $tr = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6,19])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $tr = array_merge($tr, $na);
        foreach ($tr as $k => $v){
            if(in_array($v['name'],$tr_arr)){
                $tr[$k]['selected'] = true;
            }
        }

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $re = array_merge($re, $na);
        foreach ($re as $k => $v){
            if(in_array($v['name'],$re_arr)){
                $re[$k]['selected'] = true;
            }
        }

        // 预排
        $yp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $yp = array_merge($yp, $na);
        foreach ($yp as $k => $v){
            if(in_array($v['name'],$yp_arr)){
                $yp[$k]['selected'] = true;
            }
        }

        // 后排
        $hp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $hp = array_merge($hp, $na);
        foreach ($hp as $k => $v){
            if(in_array($v['name'],$hp_arr)){
                $hp[$k]['selected'] = true;
            }
        }

        // 返回值
        return json([
            'data'=>$info, 'fc'=>json_encode($document_type),
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp)
        ]);
    }
    //批量修改
    public function Batch_edit(Request $request)
    {



        try {
            $data=$request->param();
            $field=array_filter(explode(',',$data['field']));
            $numsss=array_filter(explode(',',$data['numsss']));
            $arr=[];
            foreach ($field as $k=>$v)
            {
                foreach ($numsss as $k1=>$v1)
                {
                    if($k==$k1)
                    {
                        $arr[$v]=$v1;
                    }
                }
            }
            $res = Db::name('pj_project_profile')->wherein('id',$data['arr'])->update($arr);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json(['code'=>9999,'error'=>$e->getMessage()]);
        }

        return json(['code'=>$res]);
    }

    //文件名编号效验
    public function inspection(Request $request)
    {
        try {
            $data=$request->param();
            $res = Db::name('pj_project_profile')->wherein('Filing_Code',$data['Filing_Code'])->count();
            if($res<1){
                return json(['code'=>6666,]);
            }
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json(['code'=>9999,'error'=>$e->getMessage()]);
        }

        return json(['code'=>$res]);
    }

    //基本信息批量修改
    public function Batch_edite(Request $request)
    {
        try {
            $data=$request->param();
            $field=array_filter(explode(',',$data['field']));
            $numsss=array_filter(explode(',',$data['numsss']));
            $arr=[];
            foreach ($field as $k=>$v)
            {
                foreach ($numsss as $k1=>$v1)
                {
                    if($k==$k1)
                    {
                        $arr[$v]=$v1;
                    }
                }
            }
            $res = Db::name('pj_project_profile_text')->wherein('id',$data['arr'])->update($arr);
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