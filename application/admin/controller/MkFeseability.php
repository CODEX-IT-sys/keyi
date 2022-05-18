<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\MkInquiry as MkInquiryModel;
use app\facade\MkFeseability as MkFeseabilityModel;
use app\facade\MkInvoicing as MkInvoicingModel;
use app\facade\PjContractReview as PjContractReviewModel;
use app\facade\PjProjectDatabase as PjProjectDatabaseModel;
use app\facade\XtMessages as XtMsgModel;
use think\Controller;
use think\Request;
use think\Db;

// 来稿确认 控制器
class MkFeseability extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_feseability');
        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'Filing_Code':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['fixed']='left';
                    $colsData[$k]['sort']='true';
                    break;
                case 'Attention':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Company_Name':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Job_Name':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Pages':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Source_Text_Word_Count':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'File_Type':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Service':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Language':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Delivery_Date_Expected':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Quality_Requirements':
                    $colsData[$k]['width']=120;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Completed':
                    $colsData[$k]['width']=120;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Format_Difficulty':
                    $colsData[$k]['width'] = 120;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'Translation_Difficulty':
                    $colsData[$k]['width'] = 120;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'trre_range':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'lang_style':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'format':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'deliverables':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'other_remark':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'Customer_Requirements':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'External_Reference_File':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Project_Requirements':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['edit']='true';
                    break;
                case 'Customer_Feedback':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Feedback_Completed':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Remarks':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Filled_by':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Approval_Sales_Admin_Manager':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Approval_General_Manager':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;

                default:
                    $colsData[$k]['width']=80;
                    $colsData[$k]['hide']=true;
            }

        }

        $edit=[
            [
                'Field'=>'Attention',
                'Comment'=>'客户联系人'
            ],
            [
                'Field'=>'Company_Name',
                'Comment'=>'公司名称'
            ],
            [
                'Field'=>'File_Type',
                'Comment'=>'文件类型'
            ],
            [
                'Field'=>'Service',
                'Comment'=>'服务类型'
            ],
            [
                'Field'=>'Language',
                'Comment'=>'语种'
            ],
            [
                'Field'=>'Quality_Requirements',
                'Comment'=>'质量要求'
            ],

            [
                'Field'=>'Delivery_Date_Expected',
                'Comment'=>'客户期望提交日期'
            ],

            [
                'Field'=>'Completed',
                'Comment'=>'交付日期'
            ],
            [
                'Field'=>'Customer_Requirements',
                'Comment'=>'客户要求'
            ],
            [
                'Field'=>'External_Reference_File',
                'Comment'=>'客户参考文件'
            ],
            [
                'Field' => 'trre_range',
                'Comment' => '翻译校对范围'
            ],
            [
                'Field' => 'lang_style',
                'Comment' => '语言风格'
            ],
            [
                'Field' => 'format',
                'Comment' => '排版'
            ],
            [
                'Field' => 'deliverables',
                'Comment' => '提交内容'
            ],
            [
                'Field' => 'other_remark',
                'Comment' => '文件其他情况备注'
            ],
            [
                'Field'=>'Project_Requirements',
                'Comment'=>'生成项目需求'
            ],
            [
                'Field'=>'Remarks',
                'Comment'=>'备注'
            ],

        ];

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',4)->value('intro');

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),'editor'=>$edit,
                'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword
            ]);
        }

        // 调用模型获取列表
        $list = MkFeseabilityModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_feseability');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 查询 可供预选的 编号值
        $contract_code = MkInquiryModel::field('Contract_Number')
            ->order('id desc')->select();

        // 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();
        
        // 文件类型
        $File_Type = Db::name('xt_dict')->where('c_id',4)->select();
        
        // 服务类型
        $service_type = dict(5);
        
        // 语种
        $yy = Db::name('xt_dict')->where('c_id',1)->select();
        
        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();
        
        // 单位
        $units = Db::name('xt_dict')->where('c_id',3)->select();
        	
        // 是否首次合作
        $first = Db::name('xt_dict')->where('c_id',9)->select();

        // 项目经理 通知
        $pm = Db::name('admin')->field('id, name')->where('job_id',8)->select();

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();

        // 直接返回视图
        return view('form-Feseability', [
            'gs'=>$gs, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
			'yy'=>$yy, 'first'=>$first,'currency'=>$currency, 'units'=>$units,
            'pm'=>$pm, 'contract_code'=>$contract_code,'zl'=>$zl,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    // 查看
    public function read($id)
    {
        // 查询信息
        $res = MkFeseabilityModel::get($id);

        // 服务 多选
        $fw_arr = explode(',', $res['Service']);

        // 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();

        // 主体公司ID
        $gs_id = Db::name('xt_company')->where('cn_name',$res['Subject_Company'])
            ->whereOr('en_name',$res['Subject_Company'])->value('id');

        // 文件类型
        $File_Type = Db::name('xt_dict')->where('c_id',4)->select();

        // 服务类型
        $service_type = dict(5, $fw_arr);

        // 语种
        $yy = Db::name('xt_dict')->where('c_id',1)->select();

        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();

        // 单位
        $units = Db::name('xt_dict')->where('c_id',3)->select();

        // 是否首次合作
        $first = Db::name('xt_dict')->where('c_id',9)->select();

        // 项目经理 通知
        $pm = Db::name('admin')->field('id, name')->where('job_id',8)->select();

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();


        // 允许修改 批准项 的
        if(in_array(session('administrator')['job_id'],[1,8,9,20])){
            $show = 1;
        }else{
            $show = 0;
        }

        // 直接返回视图
        return view('form-Feseability-view',[
            'info'=>$res,'gs'=>$gs, 'gs_id'=>$gs_id, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
            'pm'=>$pm,'yy'=>$yy, 'first'=>$first,'currency'=>$currency, 'units'=>$units, 'show'=>$show,'zl'=>$zl,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = MkFeseabilityModel::get($id);

        // 服务 多选
        $fw_arr = explode(',', $res['Service']);
		
		// 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();

        // 主体公司ID
        $gs_id = Db::name('xt_company')->where('cn_name',$res['Subject_Company'])
            ->whereOr('en_name',$res['Subject_Company'])->value('id');
		
		// 文件类型
		$File_Type = Db::name('xt_dict')->where('c_id',4)->select();
		
		// 服务类型
		$service_type = dict(5, $fw_arr);
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 币种
		$currency = Db::name('xt_dict')->where('c_id',2)->select();
		
		// 单位
		$units = Db::name('xt_dict')->where('c_id',3)->select();
			
		// 是否首次合作
		$first = Db::name('xt_dict')->where('c_id',9)->select();

        // 项目经理 通知
        $pm = Db::name('admin')->field('id, name')->where('job_id',8)->select();

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();

        // 允许修改 批准项 的
        if(in_array(session('administrator')['job_id'],[1,8,9,20])){
            $show = 1;
        }else{
            $show = 0;
        }

        return view('form-Feseability-view', [
            'info'=>$res,'gs'=>$gs, 'gs_id'=>$gs_id, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
		    'pm'=>$pm, 'yy'=>$yy, 'first'=>$first,'currency'=>$currency, 'units'=>$units, 'show'=>$show,'zl'=>$zl,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    public function Batch_edit(Request $request)
    {

        // 启动事务
        Db::startTrans();

        try {
            $data=$request->param();
            $field=array_filter(explode('&',$data['field']));
            $numsss=array_filter(explode('&',$data['numsss']));
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

            $arr1=$arr;
            $arr2=$arr;
            if(isset($arr['Completed'])){
                $arr['Completed']=(int)$arr['Completed'];
            }
            $res = Db::name('mk_feseability')->wherein('id',$data['arr'])->update($arr);
            $Filing_Code=Db::name('mk_feseability')->wherein('id',$data['arr'])->field('Filing_Code')->select();
            //结算管理需要同步的信息
            $f = ['Assigned_Date','Subject_Company','Sales','Attention','Job_Name','Pages','Source_Text_Word_Count','File_Type','Service','Language',
                'Currency','Unit_Price','Units','Quote_Number','Quote_Quantity','VAT_Rate','VAT_Amount','Quote_Amount','Completed','Customer_Requirements',
                'External_Reference_File','Subject_Company_VAT_ID','Subject_Company_Address','Subject_Company_Bank_Info','Remarks'];

            foreach ($field as $key=>$val){
                if(!in_array($val, $f)){
                    unset($arr[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                $res=   Db::name('mk_invoicing')
                    ->where('Filing_Code', $v['Filing_Code'])
                    ->update($arr);
            }

            // 同步更新 项目汇总表 相关信息
            $d = ['Attention','Company_Name','File_Type','Service','Delivery_Date_Expected','Completed','Customer_Requirements','External_Reference_File',
                'Pages','Source_Text_Word_Count','Job_Name','Language','Quality_Requirements','trre_range','lang_style','format','deliverables','other_remark'];
            foreach ($field as $key=>$val){
                if(!in_array($val, $d)) {
                    unset($arr1[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                Db::name('pj_contract_review')
                    ->where('Filing_Code', $v['Filing_Code'])
                    ->update($arr1);
                //修改提前交付天数
                $c_data = Db::name('pj_contract_review')
                    ->where('Filing_Code', $v['Filing_Code'])->find();
                if(!empty($c_data["Delivery_Date_Expected"]) && !empty($c_data["Completed"])){
                    $expect = strtotime($c_data["Delivery_Date_Expected"]); //客户期望日期
                    $expect = strtotime(date('Ymd',$expect));
                    $completed = strtotime($c_data["Completed"]);//交付日期
                    $early_days = round(($completed - $expect)/86400);
                    if($early_days >100 || $early_days < -100){
                        $early_days = -999;
                    }
                    if($early_days > 0 && $early_days < 101){
                        $early_days = '*'.$early_days;
                    }
                    $c_data['Early_days'] = $early_days;
                }else{
                    $c_data['Early_days'] = 'N/A';
                }
                $up_data = [
                    'Early_days' => $c_data['Early_days']
                ];
                Db::name('pj_contract_review')
                    ->where('Filing_Code', $v['Filing_Code'])
                    ->update($up_data);
            }

            // 同步更新项目数据库表
            $da = ['Completed'];
            foreach ($field as $key=>$val){
                if(!in_array($val, $da)) {
                    unset($arr2[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                Db::name('pj_project_database')
                    ->where('Filing_Code', $v['Filing_Code'])
                    ->update($arr2);
            }


            // 提交事务
            Db::commit();
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 这是进行异常捕获
            return json(['code'=>9999,'error'=>$e->getMessage()]);
        }

        return json(['code'=>$res]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 登记日期
        $data['Assigned_Date'] = date("Ymd");

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 查询 公司编码
        $company_code = Db::name('mk_contract')
            ->where('Contract_Number', $data['Contract_Number'])
            ->value('Company_Code');

        // 获取当天的文件数
        $arr = Db::name('mk_feseability')->field('id')
            ->where('Assigned_Date',  intval(date("Ymd")))
            ->select();

        $no = count($arr);

        // 调用方法 生成 文件编号
        $data['Filing_Code'] = filing_number($company_code, $no);

        // 保存
        MkFeseabilityModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据

        $data = $request->post();
        //客户反馈
        $fk = MkFeseabilityModel::where('id', $data['id'])->find();

        if($fk['Customer_Feedback'] != $data['Customer_Feedback']){
            $feedback = 'No';
        }else{
            $feedback = $fk['Feedback_Completed'];
        }
        MkFeseabilityModel::update($data);

        // 文件编号关联
        $fc = MkFeseabilityModel::where('id', $data['id'])->value('Filing_Code');


        if(!empty($data["Delivery_Date_Expected"]) && !empty($data["Completed"])){
            $expect = strtotime($data["Delivery_Date_Expected"]); //客户期望日期
            $expect = strtotime(date('Ymd',$expect));
            $completed = strtotime($data["Completed"]);//交付日期
            $early_days = round(($completed - $expect)/86400);
            if($early_days >100 || $early_days < -100){
                $early_days = -999;
            }
            if($early_days > 0 && $early_days < 101){
                $early_days = '*'.$early_days;
            }

        }else{
            $early_days = 'N/A';
        }
        // 同步更新 交付日期
        MkInvoicingModel::where('Filing_Code', $fc)
            ->update([
                'Completed'=>$data['Completed'],
                'Quote_Number'=>$data['Quote_Number']
            ]);

        PjContractReviewModel::where('Filing_Code', $fc)
            ->update([
                'Completed'=>$data['Completed'],
                    'Early_days' => $early_days,
            ]);

        PjProjectDatabaseModel::where('Filing_Code', $fc)
            ->update(['Completed'=>$data['Completed']]);
        //同步修改项目汇总

        //增加一条sql,实现同步修改,!!拉胯
        //项目汇总同步修改
        $a=  Db::table('ky_pj_contract_review')->where('Filing_Code',$fc)->find();
        if($a){
            //判断文件类型是否为Excel,是的话需要根据源语数量换算页数
            if($data['File_Type'] == 'Excel'){
                $data['Pages'] = round($data['Source_Text_Word_Count']/400);
                if($data['Pages'] == 0 && $data['Source_Text_Word_Count'] > 0){
                    $data['Pages'] = 1;
                }
            }
            Db::table('ky_pj_contract_review')->where('Filing_Code',$fc)->update(
                [
                    'Delivery_Date_Expected'=>$data['Delivery_Date_Expected'],
                    'Attention'=>$data['Attention'],
                    'First_Cooperation'=>$data['First_Cooperation'],
                    'Sales'=>$data['Sales'],
                    'Customer_Requirements'=>$data['Customer_Requirements'],
                    'External_Reference_File'=>$data['External_Reference_File'],
                    'Customer_Feedback' => $data['Customer_Feedback'],
                    'Feedback_Completed' => $feedback,
                    'Pages' => $data['Pages'],
                    'Source_Text_Word_Count' => $data['Source_Text_Word_Count'],
                    'Job_Name' => $data['Job_Name'],
                    'Language' => $data['Language'],
                    'File_Type' => $data['File_Type'],
                    'Service' => $data['Service'],
                    'Quality_Requirements' => $data['Quality_Requirements'],
                    'trre_range' => $data['trre_range'],
                    'lang_style' => $data['lang_style'],
                    'format' => $data['format'],
                    'deliverables' => $data['deliverables'],
                    'other_remark'=>$data['other_remark']
                ]
            );
        }

        unset($data['id']);
        unset($data['Department']);
        unset($data['Company_Name']);
        unset($data['select']);
        unset($data['Delivery_Date_Expected']);
        unset($data['First_Cooperation']);
        unset($data['Project_Requirements']);
        unset($data['Request_a_Quote']);
        unset($data['Repeated_Document']);
        unset($data['PM']);
        unset($data['Approval_Sales_Admin_Manager']);
        unset($data['Approval_General_Manager']);
        unset($data['Customer_Feedback']);
        unset($data['Feedback_Completed']);
        unset($data['Quality_Requirements']);
        unset($data['trre_range']);
        unset($data['lang_style']);
        unset($data['format']);
        unset($data['deliverables']);
        unset($data['other_remark']);
        Db::table('ky_mk_invoicing')->where('Filing_Code',$fc)->update($data);






        echo "<script>window.history.go(-2)</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 单条删除
    public function delete($id)
    {
        // 调用模型删除
        MkFeseabilityModel::destroy($id);

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
            ->where('name',$name)->where('C',$controller)
            ->value('delete');

        if (empty($rw)) {

            $this->error('无权操作');

        }else if($rw == 0){

            $this->error('无权操作');
        }

        $id_arr = explode(',' , $id);

        // 调用模型删除
        foreach ($id_arr as $k => $v){
            MkFeseabilityModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 市场行政经理 批量确认
    public function batch_sales($id)
    {
        $id_arr = explode(',' , $id);

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id',$uid)->value('job_id');
//        dump($job_id);
        // 检查身份信息是否匹配
        if($job_id != 20){

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        }else{

            // 结算管理 字段 （文件接受自动写入 不批准重复写入）
            /*$js = ['Assigned_Date','Filing_Code','Sales','Attention','Department',
                'Job_Name','Pages','Source_Text_Word_Count','File_Type','Service','Completed',
                'Language','Currency','Unit_Price','Units','Quote_Number','Quote_Quantity',
                'VAT_Rate','VAT_Amount','Quote_Amount','Subject_Company', 'Subject_Company_VAT_ID',
                'Subject_Company_Address','Subject_Company_Bank_Info',
                'Customer_Requirements','External_Reference_File','Filled_by'
            ];*/

            // 项目汇总 字段
            $pj = ['Filing_Code','Job_Name','Company_Name','Sales','Attention','Department','Pages',
                'File_Type','Service','Language','Source_Text_Word_Count','Delivery_Date_Expected','Completed','PM',
                'Customer_Requirements','External_Reference_File','First_Cooperation','Quality_Requirements','trre_range','lang_style','format','deliverables','other_remark'];

            // 项目数据库 字段
            $pj_db = ['Filing_Code','Job_Name','Company_Name','Pages', 'File_Type',
                'Service','Language', 'Source_Text_Word_Count','Completed', 'Attention'];


/*
            // 结算管理 提醒消息 信息(财务)
            $jsm_data['cn_title'] = '您有新结算管理信息待处理！';
            $jsm_data['en_title'] = 'You have new Invoicing to pending';
            $jsm_data['status'] = 0;
            // 财务人员 信息群发
            $cw_name = Db::name('admin')->field('id, name')->where('status', 0)->where('job_id',14)->select();
*/


            // 项目汇总 提醒消息 信息(PM)
            $pjm_data['cn_title'] = '您有新项目汇总信息待处理！';
            $pjm_data['en_title'] = 'You have new Contract Review to pending';
            $pjm_data['status'] = 0;

            // 批量 改变状态、自动写入数据到 结算管理和项目汇总表
            foreach ($id_arr as $k => $v) {

                $i = Db::name('mk_feseability')->where('id', $v)
                    ->field('Filing_Code, Contract_Number, PM, Approval_Sales_Admin_Manager, Approval_General_Manager')
                    ->find();

                if ($i['Approval_General_Manager'] != 'Yes') {
                    Db::name('mk_feseability')->where('id', $v)->update(['Approval_Sales_Admin_Manager' => 'Yes']);
                }

                if ($i['Approval_Sales_Admin_Manager'] != 'Yes') {
                    if ($i['Approval_General_Manager'] != 'Yes') {
                        $pa01 = ['飞利浦','赛默飞','碧迪','医科达','库克','瓦里安','爱德华','梅里埃','华大智造','泛生子','柏视医疗','美天旎','苏州颐坤','凯杰','珀金埃尔默','阅尔基因','北京优迅'];
                        $pa04 = ['博士伦','安进','费森卡比','海外客户','比司特','京卫制药','法液空','华奥泰','嘉德诺','深圳罗素','神领锐医药','君实生物','朝日英达','博沃','永仁心',
                            '欧姆龙','碧篦子','上海光电','唯美','博尔迈','泰利福','布朗兄弟','品驰','贝恩','卡尔蔡司','先临三维','Vapotherm','赛诺微','明维视景','食药中心','麟科泰',
                            'Cambridge','ResApp','Invivoscribe','NanoEnTek','RapiGEN','Geneplanet','YAMAKIN','Absology','VIVO','Vapotherm','VVR','CALTH','Seegene','百普赛斯','液空芳业'];
                        $pa05 = ['拜耳海外','礼来','第一三共','美纳里尼','药审中心','武田','辉瑞','田边三菱','高德美','东丽','乐普','大冢','阿尔法','安斯泰来','Aptar','美信美达','英诺湖','辉瑞 Pfizer','乐普生物'];
                        $pa06 = ['创领心律','雅培','微创','艾力康','上海以心','Cryolife','健世科技','沃比医疗','启明医疗','微创心脉','微创神通'];
                        $pa12 = ['费森','巴尔特','科利耳','施乐辉','百多力','贝朗','捷迈','登士柏','蒙太因','日机装','士卓曼','索诺瓦','爱齐','唯炜澜谛','皆美','赛德迪康','盖思特利','康乐保','时代天使','安思尔'];
                        //$js_data = Db::name('mk_feseability')->where('id', $v)->field($js)->find();

                         $pa02 = [];
                         $pa03 = [];
                         $pa08= [];
                         $pa09 = [];
                         $pa10 = [];
                         $pa11 = [];


                        $pj_data = Db::name('mk_feseability')->where('id', $v)->field($pj)->find();
                        $pj_data['Date'] = substr($pj_data['Filing_Code'], 2, 8);

                        //判断文件类型是否为Excel,是的话需要根据源语数量换算页数
                        if($pj_data['File_Type'] == 'Excel'){
                            $pj_data['Pages'] = round($pj_data['Source_Text_Word_Count']/400);
                            if($pj_data['Pages'] == 0 && $pj_data['Source_Text_Word_Count'] > 0){
                                $pj_data['Pages'] = 1;
                            }
                        }

                        //提前交付天数
                        if(!empty($pj_data["Delivery_Date_Expected"]) && !empty($pj_data["Completed"])){
                            $expect = strtotime($pj_data["Delivery_Date_Expected"]); //客户期望日期
                            $expect = strtotime(date('Ymd',$expect));
                            $completed = strtotime($pj_data["Completed"]);//交付日期
                            $early_days = round(($completed - $expect)/86400);
                            if($early_days >100 || $early_days < -100){
                                $early_days = -999;
                            }
                            if($early_days > 0 && $early_days < 101){
                                $early_days = '*'.$early_days;
                            }
                            $pj_data['Early_days'] = $early_days;
                        }else{
                            $pj_data['Early_days'] = 'N/A';
                        }

                        $pj_db_data = Db::name('mk_feseability')->where('id', $v)->field($pj_db)->find();
                        $pj_db_data['Date'] = substr($pj_db_data['Filing_Code'], 2, 8);
                        //判断文件类型是否为Excel,是的话需要根据源语数量换算页数
                        if($pj_db_data['File_Type'] == 'Excel'){
                            $pj_db_data['Pages'] = round($pj_db_data['Source_Text_Word_Count']/400);
                            if($pj_db_data['Pages'] == 0 && $pj_db_data['Source_Text_Word_Count'] > 0){
                                $pj_db_data['Pages'] = 1;
                            }
                        }

                        //根据公司名称判断是哪个项目组长负责
                        $pj_company = $pj_data['Company_Name'];
                        if(in_array($pj_company,$pa01)){
                            $pj_data['PA'] = "PA01";
                            $pj_db_data['PA'] = "PA01";
                        }elseif(in_array($pj_company,$pa02)){
                            $pj_data['PA'] = "PA02";
                            $pj_db_data['PA'] = "PA02";
                        }elseif(in_array($pj_company,$pa03)){
                            $pj_data['PA'] = "PA03";
                            $pj_db_data['PA'] = "PA03";
                        }elseif(in_array($pj_company,$pa04)){
                            $pj_data['PA'] = "PA04";
                            $pj_db_data['PA'] = "PA04";
                        }elseif(in_array($pj_company,$pa05)){
                            $pj_data['PA'] = "PA05";
                            $pj_db_data['PA'] = "PA05";
                        }elseif(in_array($pj_company,$pa06)){
                            $pj_data['PA'] = "PA06";
                            $pj_db_data['PA'] = "PA06";
                        }elseif(in_array($pj_company,$pa08)){
                            $pj_data['PA'] = "PA08";
                            $pj_db_data['PA'] = "PA08";
                        }elseif(in_array($pj_company,$pa09)){
                            $pj_data['PA'] = "PA09";
                            $pj_db_data['PA'] = "PA09";
                        }elseif(in_array($pj_company,$pa10)){
                            $pj_data['PA'] = "PA10";
                            $pj_db_data['PA'] = "PA10";
                        }elseif(in_array($pj_company,$pa11)){
                            $pj_data['PA'] = "PA11";
                            $pj_db_data['PA'] = "PA11";
                        }elseif(in_array($pj_company,$pa12)){
                            $pj_data['PA'] = "PA12";
                            $pj_db_data['PA'] = "PA12";
                        }else{
                            $pj_data['PA'] = "";
                            $pj_db_data['PA'] = "";
                        }


                        PjContractReviewModel::create($pj_data);

                        PjProjectDatabaseModel::create($pj_db_data);

                        /*$jsm_data['content'] = 'Filing_Code: '. $i['Filing_Code'];
                        foreach ($cw_name as $key => $val){
                            $jsm_data['name'] = $val['name'];
                            XtMsgModel::create($jsm_data);
                        }*/

                        $pjm_data['name'] = $i['PM'];
                        $pjm_data['content'] = 'Filing_Code: '. $i['Filing_Code'];

                        XtMsgModel::create($pjm_data);
                    }
                }
            }

            // 返回数据
            return json(['msg' => '操作成功']);
        }
    }

    // 总经理/项目经理 批量确认
    public function batch_gm($id)
    {
        $id_arr = explode(',' , $id);

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id',$uid)->value('job_id');

        // 检查身份信息是否匹配
        if(!in_array($job_id, [1,8,9])){

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        }else{

            // 结算管理 字段
            /*$js = ['Assigned_Date','Filing_Code','Sales','Attention','Department',
                'Job_Name','Pages','Source_Text_Word_Count','File_Type','Service','Completed',
                'Language','Currency','Unit_Price','Units','Quote_Number','Quote_Quantity',
                'VAT_Rate','VAT_Amount','Quote_Amount','Subject_Company', 'Subject_Company_VAT_ID',
                'Subject_Company_Address','Subject_Company_Bank_Info',
                'Customer_Requirements','External_Reference_File','Filled_by'
            ];*/

            // 项目汇总 字段
            $pj = ['Filing_Code','Job_Name','Company_Name','Sales','Attention','Department','Pages',
                'File_Type','Service','Language','Source_Text_Word_Count','Delivery_Date_Expected','Completed','PM',
                'Customer_Requirements','External_Reference_File','First_Cooperation','Quality_Requirements','trre_range','lang_style','format','deliverables','other_remark'];

            // 项目数据库 字段
            $pj_db = ['Filing_Code','Job_Name','Company_Name','Pages', 'File_Type',
                'Service','Language', 'Source_Text_Word_Count','Completed', 'Attention'];

/*
            // 结算管理 提醒消息 信息(财务)
            $jsm_data['cn_title'] = '您有新结算管理信息待处理！';
            $jsm_data['en_title'] = 'You have new Invoicing to pending';
            $jsm_data['status'] = 0;
            // 财务人员 信息群发
            $cw_name = Db::name('admin')->field('id, name')->where('status', 0)->where('job_id',14)->select();
*/

            // 项目汇总 提醒消息 信息(PM)
            $pjm_data['cn_title'] = '您有新项目汇总信息待处理！';
            $pjm_data['en_title'] = 'You have new Contract Review to pending';
            $pjm_data['status'] = 0;

            // 批量 改变状态、自动写入数据到 结算管理和项目汇总表
            foreach ($id_arr as $k => $v) {

                $i = Db::name('mk_feseability')->where('id', $v)
                    ->field('Filing_Code, Contract_Number, PM, Approval_Sales_Admin_Manager, Approval_General_Manager')
                    ->find();

                if($i['Approval_General_Manager'] != 'Yes'){
                    Db::name('mk_feseability')->where('id', $v)->update(['Approval_General_Manager' => 'Yes']);
                }

                // 不限制
                if($i['Approval_Sales_Admin_Manager'] != 'Yes') {
                    if($i['Approval_General_Manager'] != 'Yes'){
                        $pa01 = ['飞利浦','赛默飞','碧迪','医科达','库克','瓦里安','爱德华','梅里埃','华大智造','泛生子','柏视医疗','美天旎','苏州颐坤','凯杰','珀金埃尔默','阅尔基因','北京优迅'];
                        $pa04 = ['博士伦','安进','费森卡比','海外客户','比司特','京卫制药','法液空','华奥泰','嘉德诺','深圳罗素','神领锐医药','君实生物','朝日英达','博沃','永仁心',
                            '欧姆龙','碧篦子','上海光电','唯美','博尔迈','泰利福','布朗兄弟','品驰','贝恩','卡尔蔡司','先临三维','Vapotherm','赛诺微','明维视景','食药中心','麟科泰',
                            'Cambridge','ResApp','Invivoscribe','NanoEnTek','RapiGEN','Geneplanet','YAMAKIN','Absology','VIVO','Vapotherm','VVR','CALTH','Seegene','百普赛斯','液空芳业'];
                        $pa05 = ['拜耳海外','礼来','第一三共','美纳里尼','药审中心','武田','辉瑞','田边三菱','高德美','东丽','乐普','大冢','阿尔法','安斯泰来','Aptar','美信美达','英诺湖','辉瑞 Pfizer','乐普生物'];
                        $pa06 = ['创领心律','雅培','微创','艾力康','上海以心','Cryolife','健世科技','沃比医疗','启明医疗','微创心脉','微创神通'];
                        $pa12 = ['费森','巴尔特','科利耳','施乐辉','百多力','贝朗','捷迈','登士柏','蒙太因','日机装','士卓曼','索诺瓦','爱齐','唯炜澜谛','皆美','赛德迪康','盖思特利','康乐保','时代天使','安思尔'];
                        $pa02 = [];
                        $pa03 = [];
                        $pa08= [];
                        $pa09 = [];
                        $pa10 = [];
                        $pa11 = [];
                        //$js_data = Db::name('mk_feseability')->where('id', $v)->field($js)->find();

                        $pj_data = Db::name('mk_feseability')->where('id', $v)->field($pj)->find();
                        $pj_data['Date'] = substr($pj_data['Filing_Code'], 2, 8);
                        //判断文件类型是否为Excel,是的话需要根据源语数量换算页数
                        if($pj_data['File_Type'] == 'Excel'){
                            $pj_data['Pages'] = round($pj_data['Source_Text_Word_Count']/400);
                            if($pj_data['Pages'] == 0 && $pj_data['Source_Text_Word_Count'] > 0){
                                $pj_data['Pages'] = 1;
                            }
                        }
                        //提前交付天数
                        if(!empty($pj_data["Delivery_Date_Expected"]) && !empty($pj_data["Completed"])){
                            $expect = strtotime($pj_data["Delivery_Date_Expected"]); //客户期望日期
                            $expect = strtotime(date('Ymd',$expect));
                            $completed = strtotime($pj_data["Completed"]);//交付日期
                            $early_days = round(($completed - $expect)/86400);
                            if($early_days >100 || $early_days < -100){
                                $early_days = -999;
                            }
                            if($early_days > 0 && $early_days < 101){
                                $early_days = '*'.$early_days;
                            }
                            $pj_data['Early_days'] = $early_days;
                        }else{
                            $pj_data['Early_days'] = 'N/A';
                        }

                        $pj_db_data = Db::name('mk_feseability')->where('id', $v)->field($pj_db)->find();
                        $pj_db_data['Date'] = substr($pj_db_data['Filing_Code'], 2, 8);
                        //判断文件类型是否为Excel,是的话需要根据源语数量换算页数
                        if($pj_db_data['File_Type'] == 'Excel'){
                            $pj_db_data['Pages'] = round($pj_db_data['Source_Text_Word_Count']/400);
                            if($pj_db_data['Pages'] == 0 && $pj_db_data['Source_Text_Word_Count'] > 0){
                                $pj_db_data['Pages'] = 1;
                            }
                        }
                        //根据公司名称判断是哪个项目组长负责
                        $pj_company = $pj_data['Company_Name'];
                        if(in_array($pj_company,$pa01)){
                            $pj_data['PA'] = "PA01";
                            $pj_db_data['PA'] = "PA01";
                        }elseif(in_array($pj_company,$pa02)){
                            $pj_data['PA'] = "PA02";
                            $pj_db_data['PA'] = "PA02";
                        }elseif(in_array($pj_company,$pa03)){
                            $pj_data['PA'] = "PA03";
                            $pj_db_data['PA'] = "PA03";
                        }elseif(in_array($pj_company,$pa04)){
                            $pj_data['PA'] = "PA04";
                            $pj_db_data['PA'] = "PA04";
                        }elseif(in_array($pj_company,$pa05)){
                            $pj_data['PA'] = "PA05";
                            $pj_db_data['PA'] = "PA05";
                        }elseif(in_array($pj_company,$pa06)){
                            $pj_data['PA'] = "PA06";
                            $pj_db_data['PA'] = "PA06";
                        }elseif(in_array($pj_company,$pa08)){
                            $pj_data['PA'] = "PA08";
                            $pj_db_data['PA'] = "PA08";
                        }elseif(in_array($pj_company,$pa09)){
                            $pj_data['PA'] = "PA09";
                            $pj_db_data['PA'] = "PA09";
                        }elseif(in_array($pj_company,$pa10)){
                            $pj_data['PA'] = "PA10";
                            $pj_db_data['PA'] = "PA10";
                        }elseif(in_array($pj_company,$pa11)){
                            $pj_data['PA'] = "PA11";
                            $pj_db_data['PA'] = "PA11";
                        }elseif(in_array($pj_company,$pa12)){
                            $pj_data['PA'] = "PA12";
                            $pj_db_data['PA'] = "PA12";
                        }else{
                            $pj_data['PA'] = "";
                            $pj_db_data['PA'] = "";
                        }


                        PjContractReviewModel::create($pj_data);

                        PjProjectDatabaseModel::create($pj_db_data);

                        /*$jsm_data['content'] = 'Filing_Code: '. $i['Filing_Code'];
                        foreach ($cw_name as $key => $val){
                            $jsm_data['name'] = $val['name'];
                            XtMsgModel::create($jsm_data);
                        }*/

                        $pjm_data['name'] = $i['PM'];
                        $pjm_data['content'] = 'Filing_Code: '. $i['Filing_Code'];

                        XtMsgModel::create($pjm_data);
                    }
                }
            }

            // 返回数据
            return json(['msg' => '操作成功']);
        }
    }

    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 合同编码 获取相关信息
        $info = Db::name('mk_inquiry')
            ->where('Contract_Number', $code)
            ->find();

        // 返回值
        return json(['data'=>$info]);
    }



    public function editing(Request $request)
    {

        try {
            $data=$request->param();
            $res = Db::name('mk_feseability')->where('id',$data['id'])->update([$data['field']=>$data['value']]);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json($e->getMessage());
        }

        return json(['code'=>$res]);
    }

}