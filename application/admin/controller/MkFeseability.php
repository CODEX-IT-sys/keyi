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
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Source_Text_Word_Count':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'File_Type':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Service':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Language':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Delivery_Date_Expected':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Completed':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
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
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['edit']='true';
                    break;
                case 'Remarks':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Filled_by':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Approval_Sales_Admin_Manager':
                    $colsData[$k]['width']=180;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Approval_General_Manager':
                    $colsData[$k]['width']=180;
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
		
		// 直接返回视图
        return view('form-Feseability', [
            'gs'=>$gs, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
			'yy'=>$yy, 'first'=>$first,'currency'=>$currency, 'units'=>$units,
            'pm'=>$pm, 'contract_code'=>$contract_code
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

        // 允许修改 批准项 的
        if(in_array(session('administrator')['job_id'],[1,8,9,20])){
            $show = 1;
        }else{
            $show = 0;
        }

        // 直接返回视图
        return view('form-Feseability-view',[
            'info'=>$res,'gs'=>$gs, 'gs_id'=>$gs_id, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
            'pm'=>$pm,'yy'=>$yy, 'first'=>$first,'currency'=>$currency, 'units'=>$units, 'show'=>$show
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

        // 允许修改 批准项 的
        if(in_array(session('administrator')['job_id'],[1,8,9,20])){
            $show = 1;
        }else{
            $show = 0;
        }

        return view('form-Feseability-view', [
            'info'=>$res,'gs'=>$gs, 'gs_id'=>$gs_id, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
		    'pm'=>$pm, 'yy'=>$yy, 'first'=>$first,'currency'=>$currency, 'units'=>$units, 'show'=>$show
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
            $d = ['Attention','Company_Name','File_Type','Service','Language','Delivery_Date_Expected','Completed','Customer_Requirements','External_Reference_File'];
            foreach ($field as $key=>$val){
                if(!in_array($val, $d)) {
                    unset($arr1[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                Db::name('pj_contract_review')
                    ->where('Filing_Code', $v['Filing_Code'])
                    ->update($arr1);
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
        MkFeseabilityModel::update($data);

        // 文件编号关联
        $fc = MkFeseabilityModel::where('id', $data['id'])->value('Filing_Code');

        // 同步更新 交付日期
        MkInvoicingModel::where('Filing_Code', $fc)
            ->update([
                'Completed'=>$data['Completed'],
                'Quote_Number'=>$data['Quote_Number']
            ]);

        PjContractReviewModel::where('Filing_Code', $fc)
            ->update(['Completed'=>$data['Completed']]);

        PjProjectDatabaseModel::where('Filing_Code', $fc)
            ->update(['Completed'=>$data['Completed']]);
        //同步修改项目汇总

        //增加一条sql,实现同步修改,!!拉胯
        //项目汇总同步修改
        $a=  Db::table('ky_pj_contract_review')->where('Filing_Code',$fc)->find();
        if($a){
            Db::table('ky_pj_contract_review')->where('Filing_Code',$fc)->update(
                [
                    'Delivery_Date_Expected'=>$data['Delivery_Date_Expected'],
                    'Attention'=>$data['Attention'],
                    'First_Cooperation'=>$data['First_Cooperation'],
                    'Sales'=>$data['Sales'],
                    'Customer_Requirements'=>$data['Customer_Requirements'],
                    'External_Reference_File'=>$data['External_Reference_File'],

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
                'Customer_Requirements','External_Reference_File','First_Cooperation'];

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

                        //$js_data = Db::name('mk_feseability')->where('id', $v)->field($js)->find();

                        $pj_data = Db::name('mk_feseability')->where('id', $v)->field($pj)->find();
                        $pj_data['Date'] = substr($pj_data['Filing_Code'], 2, 8);

                        $pj_db_data = Db::name('mk_feseability')->where('id', $v)->field($pj_db)->find();

                        //$gsqc = Db::name('mk_inquiry')->where('Contract_Number',$i['Contract_Number'])->value('Company_Full_Name');
                        //$js_data['Company_Full_Name'] = $gsqc;
                        //MkInvoicingModel::create($js_data);

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
        if(!in_array($job_id, [8,9])){

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
                'Customer_Requirements','External_Reference_File','First_Cooperation'];

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

                        //$js_data = Db::name('mk_feseability')->where('id', $v)->field($js)->find();

                        $pj_data = Db::name('mk_feseability')->where('id', $v)->field($pj)->find();
                        $pj_data['Date'] = substr($pj_data['Filing_Code'], 2, 8);

                        $pj_db_data = Db::name('mk_feseability')->where('id', $v)->field($pj_db)->find();

                        //$gsqc = Db::name('mk_inquiry')->where('Contract_Number',$i['Contract_Number'])->value('Company_Full_Name');
                        //$js_data['Company_Full_Name'] = $gsqc;
                        //MkInvoicingModel::create($js_data);

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