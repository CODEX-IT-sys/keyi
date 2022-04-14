<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\MkContract as MkContractModel;
use app\facade\MkInquiry as MkInquiryModel;
use app\facade\MkInquiryFile as MkInquiryFileModel;
use app\facade\MkQuote as MkQuoteModel;
use app\facade\MkQuoteTable as MkQuoteTableModel;
use app\facade\XtMessages as XtMsgModel;
use think\Controller;
use think\Request;
use think\Db;

// 来稿需求 控制器
class MkInquiry extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_inquiry');

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',3)->value('intro');

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),
                'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword
            ]);
        }

        // 调用模型获取列表
        $list = MkInquiryModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 显示 文件信息列表
    public function file_index(Request $request, $field = '', $keyword = '', $limit = 50, $id)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_inquiry_file');

        // 判断管理层
        if(in_array(session('administrator')['job_id'],[1,8,9,20])){
            // 查询 所有文件列表
            $list = Db::name('mk_inquiry_file')
                ->where('i_id',$id)
                ->where($field, 'like', "%$keyword%")
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);
        }else{
            // 查询 自己填写的 列表
            $list = Db::name('mk_inquiry_file')
                ->where('i_id',$id)
                ->where($field, 'like', "%$keyword%")
                ->where('Filled_by', session('administrator')['name'])
                ->where('delete_time',0)
                ->order('id desc')
                ->paginate($limit);
        }

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field'=>$colsData, 'i_id'=>$id, 'colsData' => json_encode($colsData)
            ]);
        }

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_inquiry');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 查询 可供预选的 编号值
        $contract_code = MkContractModel::field('Contract_Number')->order('id desc')->select();

		// 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();
	
		// 是否首次合作
		$first = Db::name('xt_dict')->where('c_id',9)->select();

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();

        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();

        // 项目经理 通知
        $pm = Db::name('admin')->field('id, name')->where(['job_id'=>8,'status'=> 0,'delete_time'=>0])->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();


        // 直接返回视图
        return view('form-Inquiry', [
            'gs'=>$gs, 'pm'=>$pm, 'first'=>$first, 'vat_rate'=>$vat_rate,
            'currency'=>$currency, 'contract_code'=>$contract_code,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    // 显示新建 文件信息 的表单页
    public function file_create($i_id)
    {
        // 文件类型
        $File_Type = Db::name('xt_dict')->where('c_id',4)->select();

        // 服务
        $service_type = dict(5);

        // 语种
        $yy = Db::name('xt_dict')->where('c_id',1)->select();

        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();

        // 单位
        $units = word(3);

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        //需求模板
        $template = Db::name('mk_inquiry')->where('id',$i_id)->field('trre_range,lang_style,format,deliverables,other_remark')->find();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();
        // 直接返回视图
        return view('file_Inquiry', [
            'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),'zl'=>$zl,
            'yy'=>$yy, 'i_id'=>$i_id, 'currency'=>$currency, 'units'=>$units, 'vat_rate'=>$vat_rate,'info'=>$template,
             'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    // 查看
    public function read($id)
    {
        // 查询信息
        $res = MkInquiryModel::get($id);

        // 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();

        // 主体公司ID
        $gs_id = Db::name('xt_company')->where('cn_name',$res['Subject_Company'])
            ->whereOr('en_name',$res['Subject_Company'])->value('id');

        // 是否首次合作
        $first = Db::name('xt_dict')->where('c_id',9)->select();

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();

        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();

        // 项目经理 通知
        $pm = Db::name('admin')->field('id, name')->where(['job_id'=>8,'status'=> 0,'delete_time'=>0])->select();



        // 直接返回视图
        return view('form-Inquiry-view', [
            'info'=>$res,'gs'=>$gs, 'gs_id'=>$gs_id, 'pm'=>$pm,
            'vat_rate'=>$vat_rate,'currency'=>$currency, 'first'=>$first,

        ]);
    }

    // 编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = MkInquiryModel::get($id);
		
		// 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();

        // 主体公司ID
        $gs_id = Db::name('xt_company')->where('cn_name',$res['Subject_Company'])
            ->whereOr('en_name',$res['Subject_Company'])->value('id');
			
		// 是否首次合作
		$first = Db::name('xt_dict')->where('c_id',9)->select();

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();

        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();

        // 项目经理 通知
        $pm = Db::name('admin')->field('id, name')->where(['job_id'=>8,'status'=> 0,'delete_time'=>0])->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();
        return view('form-Inquiry-view', [
            'info'=>$res, 'gs'=>$gs, 'gs_id'=>$gs_id, 'pm'=>$pm,
            'vat_rate'=>$vat_rate,'currency'=>$currency, 'first'=>$first,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    // 文件信息 编辑视图
    public function file_edit($id, $i_id)
    {
        // 查询信息
        $res = Db::name('mk_inquiry_file')->where('id',$id)->find();

        // 服务 多选
        $fw_arr = explode(',', $res['Service']);

        // 文件类型
        $File_Type = Db::name('xt_dict')->where('c_id',4)->select();

        // 服务类型
        $service = dict(5, $fw_arr);

        // 语种
        $yy = Db::name('xt_dict')->where('c_id',1)->select();

        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();

        // 单位
        $units = Db::name('xt_dict')->where('c_id',3)->select();

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        if(!$res['bh_date']){
            $res['bh_date'] = date('Ymd',time());
        }

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();

        // 直接返回视图
        return view('file_Inquiry-view', [
            'File_Type'=>$File_Type, 'service'=>json_encode($service), 'yy'=>$yy,'zl'=>$zl,
            'i_id'=>$i_id, 'currency'=>$currency, 'units'=>$units, 'info'=>$res, 'vat_rate'=>$vat_rate,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 来稿日期
        $data['Inquiry_Date'] = date("Ymd");

        // 更新时间
        $data['Update_Date'] = date("Ymd");

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        MkInquiryModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 新建 保存 文件信息数据
    public function file_save(Request $request, $i_id)
    {
        // 获取提交的数据
        $data = $request->post();

        // 来稿日期
        $data['Inquiry_Date'] = date("Ymd");
        // 更新时间
        $data['Update_Date'] = date("Ymd");
        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 合同编号
        $data['Contract_Number'] = Db::name('mk_inquiry')
            ->where('id', $i_id)->value('Contract_Number');

        // 多选ID
        unset($data['select']);

        // 保存
        Db::name('mk_inquiry_file')->insert($data);



        /*以下为 数据自动关联写入功能*/
        // 判断订单状态(接收 自动写入 来稿确认)
        if($data['Order_Status'] == 'Accepted'){

            // 查询 来稿需求 主表信息
            $i = Db::name('mk_inquiry')->where('id',$i_id)
                ->field('Contract_Number,Subject_Company,Sales,Attention,Department,Company_Name,Company_Full_Name,
                Quote_Number, First_Cooperation, Request_a_Quote, PM, Currency,
                Subject_Company_VAT_ID, Subject_Company_Address, Subject_Company_Bank_Info')->find();

            // 筛选 来稿确认表 字段
            $f_field = ['Job_Name','Pages','Source_Text_Word_Count','File_Type','Service', 'VAT_Rate',
                'Language','Currency','Unit_Price','Units','Quote_Quantity','Quote_Amount','VAT_Amount',
                'Delivery_Date_Expected','Quality_Requirements','Customer_Requirements','External_Reference_File', 'Remarks','trre_range','lang_style','format','deliverables','other_remark'];

            // 筛选 结算管理表 字段
            $in_field = ['Job_Name','Pages','Source_Text_Word_Count','File_Type','Service', 'VAT_Rate',
                'Language','Currency','Unit_Price','Units','Quote_Quantity','Quote_Amount','VAT_Amount',
                'Customer_Requirements','External_Reference_File', 'Remarks'];

            // 筛选存在 来稿确认表、结算管理表 字段
            $fz_data = []; $iz_data = [];
            foreach ($data as $k => $v){
                if(in_array($k, $f_field)){
                    $fz_data[$k] = $v;
                }
                if(in_array($k, $in_field)){
                    $iz_data[$k] = $v;
                }
            }

            // 合并数组 得到 来稿确认表 关联写入的字段
            $f_data = array_merge($fz_data,$i);
            // 合并数组 得到 结算管理表 关联写入的字段
            $in_data = array_merge($iz_data,$i);

            // 来稿存公司名、结算存公司全名
            unset($f_data['Company_Full_Name']);
            unset($in_data['Company_Name']);
            unset($in_data['Contract_Number']);
            unset($in_data['Department']);
            unset($in_data['First_Cooperation']);
            unset($in_data['Request_a_Quote']);
            unset($in_data['PM']);
            unset($in_data['VAT_Amount']);

            $now = $data['bh_date'];
            if(empty($now)){
                $now = date("Ymd");
            }
            // 查询 公司编码
            $company_code = Db::name('mk_contract')
                ->where('Contract_Number', $i['Contract_Number'])->value('Company_Code');

            // 获取当天的文件数
            $arr = Db::name('mk_feseability')->field('id')
                ->where('Assigned_Date',  intval($now))->select();
            $no = count($arr);

            /* 调用方法 生成 文件编号*/

            $f_data['Filing_Code'] = filing_number($company_code, $no,$now);


            // 来稿 委托日期
            $f_data['Assigned_Date'] = $now;
            // 来稿 填表人
            $f_data['Filled_by'] = session('administrator')['name'];

            // 结算 文件编号
            $in_data['Filing_Code'] = $f_data['Filing_Code'];
            // 结算 委托日期
            $in_data['Assigned_Date'] = $now;
            // 结算 填表人
            $in_data['Filled_by'] = session('administrator')['name'];



            /*自动写入 来稿确认表 数据*/
            if($data['deliver_date']!=''){
                $f_data['Completed']=date('Ymd',strtotime($data['deliver_date']));
                $in_data['Completed']=date('Ymd',strtotime($data['deliver_date']));
            }

            Db::name('mk_feseability')->insert($f_data);


            /*自动写入 结算管理表 数据*/
            Db::name('mk_invoicing')->insert($in_data);


            // 提醒消息 信息
            $m_data['cn_title'] = '您有新来稿待批准，请尽快确认！';
            $m_data['en_title'] = 'You have new file to confirm';

            $m_data['content'] = 'Filing_Code: '. $f_data['Filing_Code'];
            $m_data['status'] = 0;

            // 通知 市场行政经理
            $res = Db::name('admin')->field('name')->where('job_id', 20)
                ->where('status',0)->where('delete_time',0)->find();
            $m_data['name'] = $res['name'];
            XtMsgModel::create($m_data);

            // 通知 总经理
            $res = Db::name('admin')->field('name')->where('job_id', 9)
                ->where('status',0)->where('delete_time',0)->find();
            $m_data['name'] = $res['name'];
            XtMsgModel::create($m_data);
        }

        // 返回操作结果
        $this->redirect('file_index',['id'=>$i_id]);
    }


    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 更新时间
        $data['Update_Date'] = date("Ymd");

        MkInquiryModel::update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 文件信息 更新
    public function file_update(Request $request, $i_id)
    {
        // 获取提交的数据
        $data = $request->post();

        // 更新时间
        $data['Update_Date'] = date("Ymd");

        // 多选ID
        unset($data['select']);

        // 更改数据前 查询之前的状态
        $order_status = Db::name('mk_inquiry_file')->where('id',$data['id'])->value('Order_Status');

        // 更新数据
        Db::name('mk_inquiry_file')->update($data);

        // 仅限更改订单状态 为接收 才触发相关逻辑
        if($order_status != 'Accepted'){

            // 判断 订单状态(接收 自动写入 来稿确认)
            if($data['Order_Status'] == 'Accepted'){

                $i = Db::name('mk_inquiry')->where('id',$i_id)
                    ->field('Contract_Number,Subject_Company,Sales,Attention,Department,Company_Name,Company_Full_Name,
                    Quote_Number, First_Cooperation, Request_a_Quote, PM, Currency,
                    Subject_Company_VAT_ID, Subject_Company_Address, Subject_Company_Bank_Info')->find();

                // 筛选 来稿确认表 字段
                $f_field = ['Job_Name','Pages','Source_Text_Word_Count','File_Type','Service', 'VAT_Rate',
                    'Language','Currency','Unit_Price','Units','Quote_Quantity','Quote_Amount','VAT_Amount',
                    'Delivery_Date_Expected','Quality_Requirements','Customer_Requirements','External_Reference_File', 'Remarks','trre_range','lang_style','format','deliverables','other_remark'];

                // 筛选 结算管理表 字段
                $in_field = ['Job_Name','Pages','Source_Text_Word_Count','File_Type','Service', 'VAT_Rate',
                    'Language','Currency','Unit_Price','Units','Quote_Quantity','Quote_Amount','VAT_Amount',
                    'Customer_Requirements','External_Reference_File', 'Remarks',];

                // 筛选存在 来稿确认表、结算管理表 字段
                $fz_data = []; $iz_data = [];
                foreach ($data as $k => $v){
                    if(in_array($k, $f_field)){
                        $fz_data[$k] = $v;
                    }
                    if(in_array($k, $in_field)){
                        $iz_data[$k] = $v;
                    }
                }

                // 合并数组 得到 来稿确认表 关联写入的字段
                $f_data = array_merge($fz_data,$i);
                // 合并数组 得到 结算管理表 关联写入的字段
                $in_data = array_merge($iz_data,$i);

                // 来稿存公司名、结算存公司全名
                unset($f_data['Company_Full_Name']);
                unset($in_data['Company_Name']);
                unset($in_data['Contract_Number']);
                unset($in_data['Department']);
                unset($in_data['First_Cooperation']);
                unset($in_data['Request_a_Quote']);
                unset($in_data['PM']);
                unset($in_data['VAT_Amount']);


                $now = $data['bh_date'];
                if(empty($now)){
                    $now = date("Ymd");
                }

                // 查询 公司编码
                $company_code = Db::name('mk_contract')
                    ->where('Contract_Number', $i['Contract_Number'])->value('Company_Code');

                // 获取当天的文件数
                $arr = Db::name('mk_feseability')->field('id')
                    ->where('Assigned_Date',  intval($now))
                    ->select();
                $no = count($arr);

                /*调用方法 生成 文件编号*/
                $f_data['Filing_Code'] = filing_number($company_code, $no,$now);

                // 来稿 委托日期
                $f_data['Assigned_Date'] = $now;
                // 来稿 填表人
                $f_data['Filled_by'] = session('administrator')['name'];

                // 结算 文件编号
                $in_data['Filing_Code'] = $f_data['Filing_Code'];
                // 结算 委托日期
                $in_data['Assigned_Date'] = $now;
                // 结算 填表人
                $in_data['Filled_by'] = session('administrator')['name'];
                if($data['deliver_date']!=''){
                    $f_data['Completed']=date('Ymd',strtotime($data['deliver_date']));
                    $in_data['Completed']=date('Ymd',strtotime($data['deliver_date']));
                }

                /*自动写入 来稿确认表 数据*/
                Db::name('mk_feseability')->insert($f_data);

                /*自动写入 结算管理表 数据*/
                Db::name('mk_invoicing')->insert($in_data);


                // 提醒消息 信息
                $m_data['cn_title'] = '您有新来稿待批准，请尽快确认！';
                $m_data['en_title'] = 'You have new file to confirm';

                $m_data['content'] = 'Filing_Code: '. $f_data['Filing_Code'];
                $m_data['status'] = 0;

                // 通知 市场行政经理
                $res = Db::name('admin')->field('name')->where('job_id', 20)
                    ->where('status',0)->where('delete_time',0)->find();
                $m_data['name'] = $res['name'];
                XtMsgModel::create($m_data);

                // 通知 总经理
                $res = Db::name('admin')->field('name')->where('job_id', 9)
                    ->where('status',0)->where('delete_time',0)->find();
                $m_data['name'] = $res['name'];
                XtMsgModel::create($m_data);
            }
        }

        // 返回操作结果
        $this->redirect('file_index',['id'=>$i_id]);
    }

    // 单条删除
    public function delete($id)
    {
        // 调用模型删除
        MkInquiryModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 文件信息 单条删除
    public function file_delete($id)
    {
        // 调用模型删除
        MkInquiryFileModel::destroy($id);

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
            MkInquiryModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 文件信息 批量删除
    public function file_batch_delete($id)
    {
        $id_arr = explode(',' , $id);

        // 调用模型删除
        foreach ($id_arr as $k => $v){
            MkInquiryFileModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 生成报价单(多来稿文件)
    public function quotation($id)
    {
        // 文件id数值
        $id_arr = explode(',', $id);

        // 查询 文件表信息
        $res = Db::name('mk_inquiry_file')->field('i_id, Contract_Number')
            ->where('id', $id_arr[0])->find();

        // 合同编号
        $Contract_Number = $res['Contract_Number'];

        // 查询 来稿需求 主表信息
        $info = Db::name('mk_inquiry')->where('id', $res['i_id'])
            ->field('id, Subject_Company as Issued_by, Subject_Company_VAT_ID as VAT_ID, Company_Full_Name,
            Subject_Company_Address as Address, Subject_Company_Bank_Info as Bank_Info, Attention, Currency')
            ->find();

        // 根据合同编码 查询 客户信息
        $customer_info = Db::name('mk_customer')
            ->field('Company_Code, Email1, Company_Address')
            ->where('Contract_Number', $Contract_Number)
            ->where('Attention', $info['Attention'])
            ->find();

        // 公司编码
        $company_code = $customer_info['Company_Code'];

        // 报价单 同一客户公司编号延续 获取当前最大值
        $max_no = Db::name('mk_quote')->field('id')->where('To', $info['Company_Full_Name'])->select();
        $no = count($max_no) + 1;

        // 生成 报价单编码
        $info['Quote_Number'] = 'Q-' . $company_code . date('Ymd') . $no;



        // 查询/组装 客户及主体公司 相关信息
        $info['Company_Address'] = $customer_info['Company_Address'];
        $info['Email'] = $customer_info['Email1'];
        $info['Date'] = date('Ymd');
        // 字段别名
        $info['To'] = $info['Company_Full_Name'];
        unset($info['Company_Full_Name']);



        // 查询 来稿需求 附表 文件信息
        $file_info = Db::name('mk_inquiry_file')
//            ->field('id, Job_Name, Pages, File_Type, Service, Language, VAT_Rate, Unit_Price, Units, Quote_Quantity')
            ->where('id', 'in', $id_arr)->select();

        // 将文件信息 写入 报价单 表格列表信息 更新来稿文件 报价状态

        $num=0;
        $num2=0;
        foreach ($file_info as $k => $v){

            $v['Quote_Number'] = $info['Quote_Number'];
            Db::name('mk_inquiry_file')->where('id',$file_info[$k]['id'])->update(['Request_a_Quote'=>'Yes']);

             $v['Net_Amount'] = $v['Quote_Quantity'] * $v['Unit_Price'];
             $v['VAT_Amount'] = $v['Quote_Quantity'] * $v['Unit_Price'] * $v['VAT_Rate']/100;
            $v['Quote_Amount'] = $v['Net_Amount'] + $v['VAT_Amount'];
            $num2 += $v['Quote_Quantity'] * $v['Unit_Price'] * $v['VAT_Rate']/100;
            $num += $v['Net_Amount'] + $v['VAT_Amount'];

            unset($v['id']);
            MkQuoteTableModel::create($v);
        }

        // 更新 来稿需求 主附表信息及报价状态值
      $a=  Db::name('mk_inquiry')->where('id', $info['id'])
            ->update(['Request_a_Quote'=>'Yes', 'Quote_Number'=>$info['Quote_Number'],
            'VAT_Amount'=>$num2,'Quote_Amount'=>$num
            ]);

        unset($info['id']);
        // 生成 填表人
        $info['Filled_by'] = session('administrator')['name'];
//        dump($info);die;
        // 报价单 主表中写入一条关联信息
        MkQuoteModel::create($info);

        // 返回值
        return json(['msg' => 'Success']);
    }

    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 合同编码 获取相关信息
        $info = Db::name('mk_contract')
            ->field('Sales, Attention, Department, Company_Full_Name, Company_Name, VAT_Rate, Subject_Company,
             Subject_Company_VAT_ID, Subject_Company_Address, Subject_Company_Bank_Info')
            ->where('Contract_Number', $code)
            ->find();

        // 根据 合同编码 查询 客户联系人
        $customer = Db::name('mk_customer')
            ->field('Attention, Department')
            ->where('Contract_Number', $code)
            ->where('delete_time', 0)
            ->select();

        // 返回值
        return json(['data'=>$info, 'c'=>$customer]);
    }

    // 根据 客户联系人 查询 所属部门
    public function get_bm($customer)
    {
        // 根据 合同编码 获取相关信息
        $bm = Db::name('mk_customer')
            ->where('Attention', $customer)
            ->where('delete_time', 0)
            ->value('Department');

        // 返回值
        return json(['bm'=>$bm]);
    }
    public function editing(Request $request)
    {


        try {
            $data=$request->param();
            $res = Db::name('mk_inquiry')->where('id',$data['id'])->update([$data['field']=>$data['value']]);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json($e->getMessage());
        }

        return json(['code'=>$res]);
    }

    public function import()
    {

        try{
            require '../extend/PHPExcel/PHPExcel.php';
            $iid=request()->param('i_id');
            $file = request()->file('file');
            if($file) {
                $info = $file->validate(['size' => 10485760, 'ext' => 'xls,xlsx,'])->move( 'public/' . 'excel');
                if (!$info) {
                    $this->error('上传文件格式不正确');
                } else {
                    //获取上传到后台的文件名
                    $fileName = $info->getSaveName();
                    //获取文件路径
                    $filePath =   'public/' . 'excel/' . $fileName;
                    //获取文件后缀
                    $suffix = $info->getExtension();
                    // 判断哪种类型
                    if($suffix=="xlsx"){
                        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
                    }else{
                        $reader = \PHPExcel_IOFactory::createReader('Excel5');
                    }
                }
                $excel = $reader->load("$filePath",$encode = 'utf-8');

                $sheet = $excel->getSheet(0);	// 读取第一个工作表(编号从 0 开始)

                $highestRow = $sheet->getHighestRow(); 			// 取得总行数
                $highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
                $arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
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
                    $res_arr[$row-2]['Inquiry_Date']  = trim($sheet->getCell("A".$row)->getValue());
                    $res_arr[$row-2]['Contract_Number']  = trim($sheet->getCell("B".$row)->getValue());
                    $res_arr[$row-2]['Job_Name']  = trim($sheet->getCell("C".$row)->getValue());
                    $res_arr[$row-2]['Pages']  = trim($sheet->getCell("D".$row)->getValue());
                    $res_arr[$row-2]['Source_Text_Word_Count']  = trim($sheet->getCell("E".$row)->getValue());
                    $res_arr[$row-2]['File_Type']  = trim($sheet->getCell("F".$row)->getValue());
                    $res_arr[$row-2]['Service']  = trim($sheet->getCell("G".$row)->getValue());
                    $res_arr[$row-2]['Language']  = trim($sheet->getCell("H".$row)->getValue());
                    $res_arr[$row-2]['Unit_Price']  = trim($sheet->getCell("I".$row)->getValue());
                    $res_arr[$row-2]['Units']  = trim($sheet->getCell("J".$row)->getValue());
                    $res_arr[$row-2]['VAT_Rate']  = trim($sheet->getCell("K".$row)->getValue());
                    $res_arr[$row-2]['Quote_Quantity']  = trim($sheet->getCell("L".$row)->getValue());
                    if($sheet->getCell("O".$row)->getValue()==''){
                        $res_arr[$row-2]['Delivery_Date_Expected']  ='';
                    }else{
                        $res_arr[$row-2]['Delivery_Date_Expected']  =date('Y-m-d H:i',strtotime(gmdate('Y-m-d H:i',\PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("O".$row)->getValue()))));
                    }
                    $res_arr[$row-2]['Quality_Requirements']  = trim($sheet->getCell("P".$row)->getValue());
                    $res_arr[$row-2]['Customer_Requirements']  = trim($sheet->getCell("Q".$row)->getValue());
                    $res_arr[$row-2]['External_Reference_File']  = trim($sheet->getCell("R".$row)->getValue());
                    $res_arr[$row-2]['Order_Status']  = trim($sheet->getCell("S".$row)->getValue());
                    $res_arr[$row-2]['Request_a_Quote']  = trim($sheet->getCell("T".$row)->getValue());
                    $res_arr[$row-2]['Remarks']  = trim($sheet->getCell("U".$row)->getValue());
                    $res_arr[$row-2]['Filled_by']  = trim($sheet->getCell("V".$row)->getValue());
                    $res_arr[$row-2]['Update_Date']  = date("Ymd");
                    $res_arr[$row-2]['Quote_Amount']  = trim($sheet->getCell("M".$row)->getValue());
                    $res_arr[$row-2]['VAT_Amount']  = trim($sheet->getCell("N".$row)->getValue());

                    if($sheet->getCell("X".$row)->getValue()==''){
                        $res_arr[$row-2]['deliver_date']  ='';
                    }else{
                        $res_arr[$row-2]['deliver_date']  =date('Y-m-d H:i',strtotime(gmdate('Y-m-d H:i',\PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("X".$row)->getValue()))));
                    }
                    $res_arr[$row-2]['bh_date']  = trim($sheet->getCell("Y".$row)->getValue());
                    $res_arr[$row - 2]['trre_range'] = trim($sheet->getCell("Z" . $row)->getValue());
                    $res_arr[$row - 2]['lang_style'] = trim($sheet->getCell("AA" . $row)->getValue());
                    $res_arr[$row - 2]['format'] = trim($sheet->getCell("AB" . $row)->getValue());
                    $res_arr[$row - 2]['deliverables'] = trim($sheet->getCell("AC" . $row)->getValue());
                    $res_arr[$row - 2]['other_remark'] = trim($sheet->getCell("AD" . $row)->getValue());
                    $res_arr[$row-2]['i_id']  = $iid;


                }

                Db::name('mk_inquiry_file')->insertAll($res_arr);

            }

        }catch(\Exception $e){
            $this->error('执行错误',$e->getMessage());
        }
        return json(['code'=>1,'msg'=>'导入成功']);

    }


    public function adopt($id)
    {
        $id_arr = explode(',' , $id);
        // 调用模型编辑
        foreach (array_reverse($id_arr) as $k => $v){
            MkInquiryFileModel::where('id',$v)->update(['Order_Status'=>'Accepted']);
            $a=  MkInquiryFileModel::find($v);
            //查询来稿需求主表
            $i = Db::name('mk_inquiry')->where('id',$a['i_id'])
                ->field('Contract_Number,Subject_Company,Sales,Attention,Department,Company_Name,Company_Full_Name,
                    Quote_Number, First_Cooperation, Request_a_Quote, PM, Currency,
                    Subject_Company_VAT_ID, Subject_Company_Address, Subject_Company_Bank_Info')->find();
//                dump($i);
            // 筛选 来稿确认表 字段
            $f_field = ['Job_Name','Pages','Source_Text_Word_Count','File_Type','Service', 'VAT_Rate',
                'Language','Currency','Unit_Price','Units','Quote_Quantity','Quote_Amount','VAT_Amount',
                'Delivery_Date_Expected','Quality_Requirements','Customer_Requirements','External_Reference_File', 'Remarks'];

            // 筛选 结算管理表 字段
            $in_field = ['Job_Name','Pages','Source_Text_Word_Count','File_Type','Service', 'VAT_Rate',
                'Language','Currency','Unit_Price','Units','Quote_Quantity','Quote_Amount','VAT_Amount',
                'Customer_Requirements','External_Reference_File', 'Remarks'];

            // 筛选存在 来稿确认表、结算管理表 字段
            $fz_data = []; $iz_data = [];

//            dump($a);
//            foreach ($a as $key => $val){
//                dump($val);
//                if(in_array($key, $f_field)){
//                    $fz_data[$key] = $val;
//                }
//                if(in_array($key, $in_field)){
//                    $iz_data[$key] = $val;
//                }
//            }
//            die;
//            dump($fz_data);
//            dump($iz_data);

            $fz_data['Pages']=$a['Pages'];
            $fz_data['Job_Name']=$a['Job_Name'];
            $fz_data['Source_Text_Word_Count']=$a['Source_Text_Word_Count'];
            $fz_data['File_Type']=$a['File_Type'];
            $fz_data['Service']=$a['Service'];
            $fz_data['Language']=$a['Language'];
            $fz_data['Unit_Price']=$a['Unit_Price'];
            $fz_data['Units']=$a['Units'];
            $fz_data['VAT_Rate']=$a['VAT_Rate'];
            $fz_data['Quote_Quantity']=$a['Quote_Quantity'];
            $fz_data['Quote_Amount']=$a['Quote_Amount'];
            $fz_data['Delivery_Date_Expected']=$a['Delivery_Date_Expected'];
            $fz_data['VAT_Amount']=$a['VAT_Amount'];
            $fz_data['Customer_Requirements']=$a['Customer_Requirements'];
            $fz_data['External_Reference_File']=$a['External_Reference_File'];
            $fz_data['Remarks']=$a['Remarks'];
            $fz_data['Quality_Requirements']=$a['Quality_Requirements'];
            $fz_data['trre_range'] = $a['trre_range'];
            $fz_data['lang_style'] = $a['lang_style'];
            $fz_data['format'] = $a['format'];
            $fz_data['deliverables'] = $a['deliverables'];
            $fz_data['other_remark'] = $a['other_remark'];



            $iz_data['Pages']=$a['Pages'];
            $iz_data['Job_Name']=$a['Job_Name'];
            $iz_data['Source_Text_Word_Count']=$a['Source_Text_Word_Count'];
            $iz_data['File_Type']=$a['File_Type'];
            $iz_data['Service']=$a['Service'];
            $iz_data['Language']=$a['Language'];
            $iz_data['Unit_Price']=$a['Unit_Price'];
            $iz_data['Units']=$a['Units'];
            $iz_data['VAT_Rate']=$a['VAT_Rate'];
            $iz_data['Quote_Quantity']=$a['Quote_Quantity'];
            $iz_data['Quote_Amount']=$a['Quote_Amount'];
            $iz_data['VAT_Amount']=$a['VAT_Amount'];
            $iz_data['Customer_Requirements']=$a['Customer_Requirements'];
            $iz_data['External_Reference_File']=$a['External_Reference_File'];
            $iz_data['Remarks']=$a['Remarks'];

            // 合并数组 得到 来稿确认表 关联写入的字段
            $f_data = array_merge($fz_data,$i);
            // 合并数组 得到 结算管理表 关联写入的字段
            $in_data = array_merge($iz_data,$i);

            // 来稿存公司名、结算存公司全名
            unset($f_data['Company_Full_Name']);
            unset($in_data['Company_Name']);
            unset($in_data['Contract_Number']);
            unset($in_data['Department']);
            unset($in_data['First_Cooperation']);
            unset($in_data['Request_a_Quote']);
            unset($in_data['PM']);
            unset($in_data['VAT_Amount']);

            $now = $a['bh_date'];
            if(empty($now)){
                $now = date("Ymd");
            }
            // 查询 公司编码
            $company_code = Db::name('mk_contract')
                ->where('Contract_Number', $i['Contract_Number'])->value('Company_Code');

            // 获取当天的文件数
            $arr = Db::name('mk_feseability')->field('id')
                ->where('Assigned_Date',  intval($now))
                ->select();
            $no = count($arr);

            /*调用方法 生成 文件编号*/

            $f_data['Filing_Code'] = filing_number($company_code, $no,$now);


            // 来稿 委托日期
            $f_data['Assigned_Date'] = $now;
            // 来稿 填表人
            $f_data['Filled_by'] = session('administrator')['name'];

            // 结算 文件编号
            $in_data['Filing_Code'] = $f_data['Filing_Code'];
            // 结算 委托日期
            $in_data['Assigned_Date'] = $now;
            // 结算 填表人
            $in_data['Filled_by'] = session('administrator')['name'];
            if($a['deliver_date']!=''){
                $f_data['Completed']=date('Ymd',strtotime($a['deliver_date']));
                $in_data['Completed']=date('Ymd',strtotime($a['deliver_date']));
            }

            /*自动写入 来稿确认表 数据*/
            Db::name('mk_feseability')->insert($f_data);

            /*自动写入 结算管理表 数据*/
            Db::name('mk_invoicing')->insert($in_data);


            // 提醒消息 信息
            $m_data['cn_title'] = '您有新来稿待批准，请尽快确认！';
            $m_data['en_title'] = 'You have new file to confirm';

            $m_data['content'] = 'Filing_Code: '. $f_data['Filing_Code'];
            $m_data['status'] = 0;

            // 通知 市场行政经理
            $res = Db::name('admin')->field('name')->where('job_id', 20)
                ->where('status',0)->where('delete_time',0)->find();
            $m_data['name'] = $res['name'];
            XtMsgModel::create($m_data);

            // 通知 总经理
            $res = Db::name('admin')->field('name')->where('job_id', 9)
                ->where('status',0)->where('delete_time',0)->find();
            $m_data['name'] = $res['name'];
            XtMsgModel::create($m_data);
        }
        // 返回数据
        return json(['msg' => '成功']);
    }

}