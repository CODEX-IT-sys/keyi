<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\MkFeseability as MkFeseabilityModel;
use app\facade\MkInvoicing as MkInvoicingModel;
use app\facade\MkInvoice as MkInvoiceModel;
use app\facade\MkInvoiceTable as MkInvoiceTableModel;
use app\facade\MkFapiao as MkFapiaoModel;
use think\Controller;
use think\Request;
use think\Db;

// 结算管理 控制器
class MkInvoicing extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_invoicing');
        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'payment_time':
                    $colsData[$k]['hide']='true';
                    break;
                case 'Post_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                default:
                    $colsData[$k]['width']=80;
            }

        }
        $edit=[
            [
                'Field'=>'Invoice_Number',
                'Comment'=>'请款单编号'
            ],
            [
                'Field'=>'Completed',
                'Comment'=>'交付日期'
            ],
            [
                'Field'=>'Unit_Price',
                'Comment'=>'单价'
            ],
            [
                'Field'=>'Units',
                'Comment'=>'单位'
            ],
            [
                'Field'=>'VAT_Rate',
                'Comment'=>'税率'
            ],
            [
                'Field'=>'Invoicing_Date',
                'Comment'=>'请款日期'
            ],
            [
                'Field'=>'PO_Number',
                'Comment'=>'PO号'
            ],
            [
                'Field'=>'Status',
                'Comment'=>'状态'
            ],
            [
                'Field'=>'Fapiao_Type',
                'Comment'=>'发票类型'
            ],
            [
                'Field'=>'Fapiao_Date',
                'Comment'=>'开票日期'
            ],

            [
                'Field'=>'Fapiao_Code',
                'Comment'=>'发票编码'
            ],

            [
                'Field'=>'Date_of_Balance',
                'Comment'=>'付款日期'
            ],

        ];
        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',5)->value('intro');

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),'editor'=>$edit,
                'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword
            ]);
        }

        // 调用模型获取列表
        $list = MkInvoicingModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_invoicing');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 查询 可供预选的 编号值
        $file_code = MkFeseabilityModel::field('Filing_Code')
            ->order('id desc')->select();

		// 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();

		// 文件类型
		$File_Type = Db::name('xt_dict')->where('c_id',4)->select();
		
		// 服务类型
		$service_type =  dict(5);
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();
		
		// 币种
		$currency = Db::name('xt_dict')->where('c_id',2)->select();
		
		// 单位
		$units = Db::name('xt_dict')->where('c_id',3)->select();
					
        // 直接返回视图
        return view('form-invoicing', [
            'file_code'=>$file_code,'gs'=>$gs, 'File_Type'=>$File_Type, 'vat_rate'=>$vat_rate,
			'service_type'=>json_encode($service_type), 'yy'=>$yy, 'currency'=>$currency, 'units'=>$units
        ]);
    }

    // 查看
    public function read($id)
    {
        // 查询信息
        $res = MkInvoicingModel::get($id);

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
        $service_type =  dict(5, $fw_arr);

        // 语种
        $yy = Db::name('xt_dict')->where('c_id',1)->select();

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();

        // 币种
        $currency = Db::name('xt_dict')->where('c_id',2)->select();

        // 单位
        $units = Db::name('xt_dict')->where('c_id',3)->select();

        // 直接返回视图
        return view('form-invoicing-view',[
            'info'=>$res,'gs'=>$gs, 'gs_id'=>$gs_id, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
            'yy'=>$yy, 'vat_rate'=>$vat_rate, 'currency'=>$currency, 'units'=>$units
        ]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = MkInvoicingModel::get($id);

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

        // 税率
        $vat_rate = Db::name('xt_dict')->where('c_id',16)->select();
		
		// 币种
		$currency = Db::name('xt_dict')->where('c_id',2)->select();
		
		// 单位
		$units = Db::name('xt_dict')->where('c_id',3)->select();

        return view('form-invoicing-view', [
            'info'=>$res,'gs'=>$gs, 'gs_id'=>$gs_id, 'File_Type'=>$File_Type, 'service_type'=>json_encode($service_type),
			'yy'=>$yy, 'vat_rate'=>$vat_rate, 'currency'=>$currency, 'units'=>$units
        ]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 委托日期
        $data['Assigned_Date'] = date("Ymd");

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 记录 付款完成时间 统计金额用
        if($data['Status'] == '付款完成'){
            $data['payment_time'] = date("Ymd");
        }

        // 保存
        MkInvoicingModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        //$data['Filled_by'] = session('administrator')['name'];

        // 记录 付款完成时间 统计金额用
        if($data['Status'] == '付款完成'){

            // 查询 之前的付款状态
            $res = Db::name('mk_invoicing')->where('id', $data['id'])->value('Status');

            // 记录 付款完成时间 统计金额用
            if($res != '付款完成'){
                $data['payment_time'] = date("Ymd");
            }
        }

        // 更新时间
        $data['Update_Date'] = date("Ymd");

        MkInvoicingModel::update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 单条删除
    public function delete($id)
    {
        // 调用模型删除
        MkInvoicingModel::destroy($id);

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
            MkInvoicingModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }


    // 生成请款单 (同一 主体公司 和 客户 批量生成)
    public function invoice($id)
    {

        $id_arr = explode(',', $id);
        $id_cd = count($id_arr);

        // 查询信息
        $info = Db::name('mk_invoicing')->where('id', 'in', $id_arr)
            ->field('Company_Full_Name, Attention, Customer_Address as Company_Address, Quote_Number, Invoicing_Date,
                 Subject_Company as Issued_by, Subject_Company_VAT_ID as VAT_ID, Subject_Company_Address as Address,
                 Filing_Code, Pages, File_Type, Assigned_Date as Assigned, Completed, Job_Name, Service, Language, 
                 Currency, Units, Unit_Quantity, Unit_Price, Net_Amount, VAT_Amount, Invoicing_Amount, VAT_Rate, Filled_by')
            ->select();

        // 判断 数据长度
        if($id_cd != 1){
            $gs = [];
            foreach ($info as $k => $v){
                $gs['kh'][] = $v['Company_Full_Name'];
                $gs['zt'][] = $v['Issued_by'];
            }

            $kh = array_count_values($gs['kh']);
            $zt = array_count_values($gs['zt']);

            // 多文件 同一 主体公司 和 客户 才能一起生成 请款单 （打印模板头部信息 限制）
            if(count($kh) != 1){
                return json(['code'=>0, 'msg'=>'Error,This Company_Full_Name is different']);
            }
            if(count($zt) != 1){
                return json(['code'=>0, 'msg'=>'Error,This Subject_Company is different']);
            }
        }


        // 根据 文件编码 查询 来稿确认的 合同编号、联系人
        $res = Db::name('mk_feseability')->field('Contract_Number,Attention')
            ->where('Filing_Code', $info[$id_cd-1]['Filing_Code'])->find();

        // 根据 合同编码 查询 客户信息
        $ht_info = Db::name('mk_customer')->field('Company_Code, Company_Address, Email1')
            ->where('Contract_Number', $res['Contract_Number'])->where('Attention', $res['Attention'])->find();

        // 请款单 同一客户公司编号延续 查询现有最大序号
        $max_no = Db::name('mk_invoice')->where('To', $info[$id_cd-1]['Company_Full_Name'])->field('id')->select();
        $no = count($max_no) + 1;

        // 生成请款单编码 Invoice_Number
        $code = 'I-' . $ht_info['Company_Code'] . date("Ymd") . $no;

        // 更新 结算管理表
        foreach ($id_arr as $k => $v){
            Db::name('mk_invoicing')->where('id', $v)->update([
                'Apply_to_Pay'=>'Yes', 'Invoice_Number'=> $code
            ]);
        }

        // 字段 别名 更正写入
        foreach ($info as $k => $v){
            $info[$k]['To'] = $info[$k]['Company_Full_Name'];
            $info[$k]['Email'] = $ht_info['Email1'];
            $info[$k]['Company_Address'] = $ht_info['Company_Address'];
        }


        // 主表字段
        $z_field = ['To','Attention','Company_Address','Email', 'Filled_by',
            'Invoicing_Date','Issued_by','VAT_ID','Address','Currency'
        ];

        // 附表信息
        $f_field = ['Filing_Code','Pages','File_Type',
            'Assigned','Completed','Net_Amount','VAT_Amount','Invoicing_Amount',
            'Job_Name','Service','Language','Units','Unit_Quantity','Unit_Price'
        ];

        // 预定义数组
        $z = []; $f = [];

        // 写入 请款单 主表信息
        foreach ($info as $k => $v){
            foreach ($v as $key => $val){
                if(in_array($key,$z_field)){
                    $z[$key] = $v[$key];
                }
            }
        }
        $z['Invoice_Number'] = $code;
        MkInvoiceModel::create($z);

        // 写入 请款单 附表 表格数据
        foreach ($info as $k => $v){
            foreach ($v as $key => $val){
                if(in_array($key,$f_field)){
                    $f[$k][$key] = $v[$key];
                }
            }
        }

        // 计算相关 金额
        foreach ($f as $k => $v){
            $f[$k]['Net_Amount'] = $f[$k]['Unit_Quantity'] * $f[$k]['Unit_Price'];
            $f[$k]['VAT_Amount'] = $f[$k]['Unit_Quantity'] * $f[$k]['Unit_Price'] * $info[$k]['VAT_Rate']/100;
            $f[$k]['Invoicing_Amount'] = $f[$k]['Net_Amount'] + $f[$k]['VAT_Amount'];

            $f[$k]['Invoice_Number'] = $code;

            MkInvoiceTableModel::create($f[$k]);
        }

        // 生成成功 返回单号
        return json(['code'=>0, 'msg'=>'Success,Invoice_Number:'.$code]);

        // 跳转到列表页
        //$this->redirect('mk_invoice/index',['id',$id]);
    }

    // 异步获取 关联信息
    public function get_info($code)
    {
        // 根据 合同编码 获取相关信息
        $info = Db::name('mk_feseability')
            ->where('Filing_Code', $code)
            ->find();

        // 返回值
        return json(['data'=>$info]);
    }

    // 异步获取 关联 开票信息
    public function get_kp_info($data)
    {
        // 根据 合同编码 获取相关信息
        $info = Db::name('mk_contract')
            ->field('Customer_VAT_No, Customer_Address, 
            Customer_Phone, Customer_Bank, Customer_Bank_Account')
            ->where('Company_Full_Name', $data)
            ->where('delete_time',0)
            ->find();

        // 返回值
        return json(['data'=>$info]);
    }

    public function editing(Request $request)
    {

        try {
            $data=$request->param();
            $res = Db::name('mk_invoicing')->where('id',$data['id'])->update([$data['field']=>$data['value']]);
        } catch (ValidateException $e) {
            // 这是进行验证异常捕获
            return json($e->getError());
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return json($e->getMessage());
        }

        return json(['code'=>$res]);
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
            $res = Db::name('mk_invoicing')->wherein('id',$data['arr'])->update($arr);


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
}