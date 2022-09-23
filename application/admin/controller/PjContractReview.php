<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjContractReview as PjContractReviewModel;
use think\Controller;
use think\Request;
use think\Db;

// 项目汇总 控制器
class PjContractReview extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_contract_review');

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
                    $colsData[$k]['sort']='true';
                    break;
                case 'Project_Name':
                    $colsData[$k]['width']=200;
                    break;
                case 'Job_Name':
                    $colsData[$k]['width']=300;
                    $colsData[$k]['fixed']='left';
                    $colsData[$k]['sort']='true';
                    break;
                case 'Pages':
                    $colsData[$k]['width']=60;
                    break;
                case 'Service':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'File_Category':
                    $colsData[$k]['width']=200;
                    $colsData[$k]['sort']='true';
                    break;
                case 'First_Cooperation':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Customer_Feedback':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Feedback_Completed':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'PA':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Early_days':
                    $colsData[$k]['width']=100;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Source_Text_Word_Count':
                    $colsData[$k]['width']=90;
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide']=true;
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
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Delivery_Date_Expected':
                    $colsData[$k]['style']='background-color:green;color:white';
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=141;
                    break;
                case 'Translator':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Reviser':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Translation_Start_Time':
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'File_Category':
                    $colsData[$k]['width']=180;
                    break;
                case 'Completed':
                    $colsData[$k]['width']=96;
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['style']='background-color:green;color:white';
                    break;
                case 'Pre_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Post_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Delivered_or_Not':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;
                case 'Attention':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;
                case 'Revision_Start_Time':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;

                case 'External_Reference_File':
                    $colsData[$k]['width']=180;
                    break;
                case 'Customer_Requirements':
                    $colsData[$k]['width']=180;
                    break;
                case 'trre_range':
                    $colsData[$k]['width'] = 150;
                    break;
                case 'lang_style':
                    $colsData[$k]['width'] = 150;
                    break;
                case 'format':
                    $colsData[$k]['width'] = 150;
                    break;
                case 'deliverables':
                    $colsData[$k]['width'] = 180;
                    break;
                case 'other_remark':
                    $colsData[$k]['width'] = 180;
                    break;
                default:
                    $colsData[$k]['width']=80;

            }

        }

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');
        $edit=[
            [
                'Field'=>'File_Category',
                'Comment'=>'文件分类'
            ],
            [
            'Field'=>'Service',
            'Comment'=>'服务'
            ],
            [
                'Field'=>'Format_Difficulty',
                'Comment'=>'排版难易程度'
            ],
            [
                'Field'=>'Translation_Difficulty',
                'Comment'=>'翻译难易程度'
            ],
            [
                'Field'=>'Completed',
                'Comment'=>'交付日期'
            ],
            [
                'Field'=>'Translator',
                'Comment'=>'翻译人员'
            ],
            [
                'Field'=>'Translation_Start_Time',
                'Comment'=>'翻译开始时间'
            ],
            [
                'Field'=>'Translation_Delivery_Time',
                'Comment'=>'翻译交付时间'
            ],
            [
                'Field'=>'Pre_Formatter',
                'Comment'=>'预排版人员'
            ],
            [
                'Field'=>'Pre_Format_Delivery_Time',
                'Comment'=>'预排版交付时间'
            ],
            [
                'Field'=>'Reviser',
                'Comment'=>'校对人员'
            ],
            [
                'Field'=>'Revision_Start_Time',
                'Comment'=>'校对开始时间'
            ],
            [
                'Field'=>'Revision_Delivery_Time',
                'Comment'=>'校对交付时间'
            ],
            [
                'Field'=>'Revise_Style',
                'Comment'=>'校对类型'
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
                'Field'=>'Delivery_Date_Expected',
                'Comment'=>'客户期望提交日期'
            ],
            [
                'Field'=>'Delivered_or_Not',
                'Comment'=>'是否交稿'
            ],
            [
                'Field'=>'Quality_Requirements',
                'Comment'=>'质量要求'
            ],
            [
                'Field'=>'PA',
                'Comment'=>'项目组长'
            ],
            [
                'Field'=>'PM',
                'Comment'=>'项目经理'
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
        $list = PjContractReviewModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    //交稿文件
    public function handover(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_contract_review');

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
                    $colsData[$k]['sort']='true';
                    break;
                case 'Project_Name':
                    $colsData[$k]['width']=200;
                    break;
                case 'Job_Name':
                    $colsData[$k]['width']=300;
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
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Delivery_Date_Expected':
                    $colsData[$k]['style']='background-color:green;color:white';
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=141;
                    break;
                case 'Translator':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Reviser':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Translation_Start_Time':
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'File_Category':
                    $colsData[$k]['width']=180;
                    break;
                case 'Completed':
                    $colsData[$k]['width']=96;
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['style']='background-color:green;color:white';
                    break;
                case 'Pre_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Post_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Delivered_or_Not':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;
                case 'Attention':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;
                case 'Revision_Start_Time':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;

                case 'External_Reference_File':
                    $colsData[$k]['width']=180;
                    break;
                case 'Customer_Requirements':
                    $colsData[$k]['width']=180;
                    break;
                default:
                    $colsData[$k]['width']=80;

            }

        }

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');
        $edit=[
            [
                'Field'=>'File_Category',
                'Comment'=>'文件分类'
            ],
            [
                'Field'=>'Format_Difficulty',
                'Comment'=>'排版难易程度'
            ],
            [
                'Field'=>'Translation_Difficulty',
                'Comment'=>'翻译难易程度'
            ],
            [
                'Field'=>'Completed',
                'Comment'=>'交付日期'
            ],
            [
                'Field'=>'Translator',
                'Comment'=>'翻译人员'
            ],
            [
                'Field'=>'Translation_Start_Time',
                'Comment'=>'翻译开始时间'
            ],
            [
                'Field'=>'Translation_Delivery_Time',
                'Comment'=>'翻译交付时间'
            ],
            [
                'Field'=>'Pre_Formatter',
                'Comment'=>'预排版人员'
            ],
            [
                'Field'=>'Pre_Format_Delivery_Time',
                'Comment'=>'预排版交付时间'
            ],
            [
                'Field'=>'Reviser',
                'Comment'=>'校对人员'
            ],
            [
                'Field'=>'Revision_Start_Time',
                'Comment'=>'校对开始时间'
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
                'Field'=>'Delivered_or_Not',
                'Comment'=>'是否交稿'
            ],
            [
                'Field'=>'Quality_Requirements',
                'Comment'=>'质量要求'
            ],
            [
                'Field'=>'PA',
                'Comment'=>'项目组长'
            ],
            [
                'Field'=>'PM',
                'Comment'=>'项目经理'
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
        $cate = 'Yes';
        // 调用模型获取列表
        $list = PjContractReviewModel::getList($search_type, $field, $keyword, $limit, $cate);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    //未交稿文件
    public function unhandover(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_contract_review');

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
                    $colsData[$k]['sort']='true';
                    break;
                case 'Project_Name':
                    $colsData[$k]['width']=200;
                    break;
                case 'Job_Name':
                    $colsData[$k]['width']=300;
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
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'Delivery_Date_Expected':
                    $colsData[$k]['style']='background-color:green;color:white';
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=141;
                    break;
                case 'Translator':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Reviser':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Translation_Start_Time':
                    $colsData[$k]['width']=150;
                    $colsData[$k]['sort']='true';
                    break;
                case 'File_Category':
                    $colsData[$k]['width']=180;
                    break;
                case 'Completed':
                    $colsData[$k]['width']=96;
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['style']='background-color:green;color:white';
                    break;
                case 'Pre_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Post_Formatter':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=100;
                    break;
                case 'Delivered_or_Not':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;
                case 'Attention':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;
                case 'Revision_Start_Time':
                    $colsData[$k]['sort']='true';
                    $colsData[$k]['width']=180;
                    break;

                case 'External_Reference_File':
                    $colsData[$k]['width']=180;
                    break;
                case 'Customer_Requirements':
                    $colsData[$k]['width']=180;
                    break;
                default:
                    $colsData[$k]['width']=80;

            }

        }

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');
        $edit=[
            [
                'Field'=>'File_Category',
                'Comment'=>'文件分类'
            ],
            [
                'Field'=>'Format_Difficulty',
                'Comment'=>'排版难易程度'
            ],
            [
                'Field'=>'Translation_Difficulty',
                'Comment'=>'翻译难易程度'
            ],
            [
                'Field'=>'Completed',
                'Comment'=>'交付日期'
            ],
            [
                'Field'=>'Translator',
                'Comment'=>'翻译人员'
            ],
            [
                'Field'=>'Translation_Start_Time',
                'Comment'=>'翻译开始时间'
            ],
            [
                'Field'=>'Translation_Delivery_Time',
                'Comment'=>'翻译交付时间'
            ],
            [
                'Field'=>'Pre_Formatter',
                'Comment'=>'预排版人员'
            ],
            [
                'Field'=>'Pre_Format_Delivery_Time',
                'Comment'=>'预排版交付时间'
            ],
            [
                'Field'=>'Reviser',
                'Comment'=>'校对人员'
            ],
            [
                'Field'=>'Revision_Start_Time',
                'Comment'=>'校对开始时间'
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
                'Field'=>'Delivered_or_Not',
                'Comment'=>'是否交稿'
            ],
            [
                'Field'=>'Quality_Requirements',
                'Comment'=>'质量要求'
            ],
            [
                'Field'=>'PA',
                'Comment'=>'项目组长'
            ],
            [
                'Field'=>'PM',
                'Comment'=>'项目经理'
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
        $cate = 'No';
        // 调用模型获取列表
        $list = PjContractReviewModel::getList($search_type, $field, $keyword, $limit, $cate);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_contract_review');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {


        // 查询 可供预选的 编号值(去重)
        $file_code = Db::name('mk_feseability')->alias('f')
            ->join('ky_pj_contract_review c', 'f.Filing_Code = c.Filing_Code', 'left')
            ->field('f.Filing_Code')
            ->where('c.Filing_Code', null)
            ->order('f.id desc')
            ->limit(30000)->select();

        // N/A 选项
        $na = [['value'=>0, 'name'=>'N/A']];

		// 文件类型
		$File_Type = Db::name('xt_dict')->where('c_id',4)->select();
		
		// 文件分类
		$document_type = dict(6);
        $document_type = array_merge($document_type, $na);
		
		// 服务类型
		$service_type = dict(5);
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 排版难度
		$pb = Db::name('xt_dict')->where('c_id',7)->order('sort','asc')->select();
		
		// 翻译难度
		$fy = Db::name('xt_dict')->where('c_id',8)->order('sort','asc')->select();

        // 是否首次合作
        $first = Db::name('xt_dict')->where('c_id',9)->select();

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        // 销售
        $sales = Admin::field('name')->where('job_id', 3)
            ->where(['status'=> 0, 'delete_time'=>0])->select();


        /*人员选项*/

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

        // 项目经理
        $pm = Admin::field('name')->where(['job_id'=> 8, 'status'=> 0])->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();

        // 直接返回视图
        return view('form-contract_review', [
            'file_code'=>$file_code, 'File_Type'=>$File_Type, 'document_type'=>json_encode($document_type),
            'service_type'=>json_encode($service_type), 'pa'=>$pa, 'pm'=>$pm,
            'yy'=>$yy, 'pb'=>$pb, 'fy'=>$fy, 'first'=>$first, 'zl'=>$zl, 'sales'=>$sales,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp),
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    // 查看
    public function read($id)
    {
        // 查询信息
        $res = PjContractReviewModel::get($id);

        $fc_arr = explode(',', $res['File_Category']);

        $fw_arr = explode(',', $res['Service']);
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

        // 服务类型
        $service_type = dict(5, $fw_arr);

        // 语种
        $yy = Db::name('xt_dict')->where('c_id',1)->select();

        // 排版难度
        $pb = Db::name('xt_dict')->where('c_id',7)->order('sort','asc')->select();

        // 翻译难度
        $fy = Db::name('xt_dict')->where('c_id',8)->order('sort','asc')->select();

        // 是否首次合作
        $first = Db::name('xt_dict')->where('c_id',9)->select();

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        // 销售
        $sales = Db::name('admin')->field('name')->where('job_id', 3)
            ->where(['status'=> 0, 'delete_time'=>0])->select();



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
        $pa = Admin::field('name')->where(['job_id'=> 7, 'status'=> 0])->select();

        // 项目经理
        $pm = Admin::field('name')->where(['job_id'=> 8, 'status'=> 0])->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();

        // 直接返回视图
        return view('form-contract_review-view',[
            'File_Type'=>$File_Type, 'document_type'=>json_encode($document_type), 'service_type'=>json_encode($service_type),
            'info'=>$res, 'yy'=>$yy, 'pb'=>$pb, 'fy'=>$fy, 'first'=>$first, 'zl'=>$zl, 'sales'=>$sales, 'pm'=>$pm,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjContractReviewModel::get($id);

        $fc_arr = explode(',', $res['File_Category']);

        $fw_arr = explode(',', $res['Service']);
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
		
		// 服务类型
		$service_type = dict(5, $fw_arr);
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 排版难度
		$pb = Db::name('xt_dict')->where('c_id',7)->order('sort','asc')->select();
		
		// 翻译难度
		$fy = Db::name('xt_dict')->where('c_id',8)->order('sort','asc')->select();

        // 是否首次合作
        $first = Db::name('xt_dict')->where('c_id',9)->select();

        // 质量要求
        $zl = Db::name('xt_dict')->where('c_id',10)->select();

        // 销售
        $sales = Db::name('admin')->field('name')->where('job_id', 3)
            ->where(['status'=> 0, 'delete_time'=>0])->select();



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

        // 项目经理
        $pm = Admin::field('name')->where('job_id', 8)->where('status', 0)->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();
        return view('form-contract_review-view', [
            'File_Type'=>$File_Type, 'document_type'=>json_encode($document_type), 'service_type'=>json_encode($service_type),
            'info'=>$res, 'yy'=>$yy, 'pb'=>$pb, 'fy'=>$fy, 'first'=>$first, 'zl'=>$zl, 'sales'=>$sales, 'pm'=>$pm,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
        ]);
    }

    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];
        // 文件创建时间
        $data['Date'] = substr($data['Filing_Code'], 2, 8);

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
            $data['Early_days'] = $early_days;
        }else{
            $data['Early_days'] = -999;
        }

        // 保存
        PjContractReviewModel::create($data);

        // 同步更新 项目数据库表 相关信息
        $f = ['Translator','Reviser','Pre_Formatter','Post_Formatter','Language','File_Type','File_Category',
            'Completed','Delivered_or_Not', 'File_Category', 'PA'];

        $db_data = [];
        foreach ($data as $k => $v){
            if(in_array($k, $f)){
                $db_data[$k] = $v;
            }
        }

        Db::name('pj_project_database')
            ->where('Filing_Code', $data['Filing_Code'])
            ->update($db_data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];
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
            $data['Early_days'] = $early_days;
        }

        PjContractReviewModel::update($data);

        //同步更新到来稿确认 反馈修订是否提交
        $feedback = [
            'Feedback_Completed' => $data['Feedback_Completed'],
            'Translation_Difficulty' => $data['Translation_Difficulty'],
            'Format_Difficulty' => $data['Format_Difficulty']
        ];

        Db::name('mk_feseability')
            ->where('Filing_Code', $data['Filing_Code'])
            ->update($feedback);

        //同步更新是否交稿到结算管理
        $deliver = [
            'Delivered_or_Not' => $data['Delivered_or_Not'],
            'Translation_Difficulty' => $data['Translation_Difficulty'],
            'Format_Difficulty' => $data['Format_Difficulty']
        ];
        Db::name('mk_invoicing')
            ->where('Filing_Code',$data['Filing_Code'])
            ->update($deliver);

        // 同步更新 项目数据库表 相关信息
        $d = ['Translator','Reviser','Pre_Formatter','Post_Formatter','Language','File_Type','File_Category',
            'Completed','Delivered_or_Not','File_Category', 'PA'];

        foreach ($data as $k => $v){
            if(in_array($k, $d)){
                $db_data[$k] = $v;
            }
        }

        Db::name('pj_project_database')
            ->where('Filing_Code', $data['Filing_Code'])
            ->update($db_data);
        // 同步更新 项目描述表
        $f = ['Pre_Formatter','Translator','Reviser','Post_Formatter','Language','File_Type','File_Category','Format_Difficulty','Translation_Difficulty','Comment',
            'Pre_Format_Delivery_Time','Translation_Delivery_Time',
            'Revision_Delivery_Time','Post_Format_Delivery_Time'];
        foreach ($data as $k => $v){
            if(in_array($k, $f)){
                $f_data[$k] = $v;
            }
        }

        $res = Db::name('pj_project_profile')
            ->where('Filing_Code', $data['Filing_Code'])
            ->select();
        $num = count($res);

        //如果描述表有多个相同的文件编号，就不同步修改
        if($num <= 1){
            Db::name('pj_project_profile')
                ->where('Filing_Code', $data['Filing_Code'])
                ->update($f_data);
        }

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjContractReviewModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    // 项目经理 批量确认
    public function batch_pm($id)
    {
        $id_arr = explode(',' , $id);

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id',$uid)->value('job_id');

        // 检查身份信息是否匹配
        if($job_id != 8){

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        }else{  // 改变数据状态

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
        $id_arr = explode(',' , $id);

        // 用户id
        $uid = session('administrator')['id'];

        // 查询用户身份
        $job_id = Db::name('admin')->where('id',$uid)->value('job_id');

        // 检查身份信息是否匹配
        if($job_id != 9){

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        }else{  // 改变数据状态

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
        $job_id = Db::name('admin')->where('id',$uid)->value('job_id');

        // 检查身份信息是否匹配
        if($job_id != 8){

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        }else{// 改变数据状态
            Db::name('pj_contract_review')->where('id',$id)
                ->update(['Approval_Project_Manager'=>'Yes']);

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
        $job_id = Db::name('admin')->where('id',$uid)->value('job_id');

        // 检查身份信息是否匹配
        if($job_id != 9){

            // 返回数据
            return json(['msg' => '身份不匹配,操作失败']);

        }else{// 改变数据状态
            Db::name('pj_contract_review')->where('id',$id)
                ->update(['Approval_General_Manager'=>'Yes']);

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
        $info['Company_Full_Name']= Db::table('ky_mk_invoicing')->where('Filing_Code',$code)->value('Company_Full_Name');;
        $fw_arr = explode(',', $info['Service']);

        // 服务类型
        $service = dict(5, $fw_arr);

        // 返回值
        return json(['data'=>$info, 'fw'=>json_encode($service)]);
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
            //Translation_Start_Time  Translation_Delivery_Time  Pre_Format_Delivery_Time Revision_Start_Time Revision_Delivery_Time Post_Format_Delivery_Time
//            历史中不存在的值,不允许修改
            /*foreach ($arr as $k4=>$v4)
            {
                if(in_array($k4,['Completed','Translation_Start_Time','Translation_Delivery_Time','Pre_Format_Delivery_Time','Revision_Start_Time','Revision_Delivery_Time','Post_Format_Delivery_Time'])){
                    continue;
                }
                   $num= Db::name('pj_contract_review')->where($k4,$v4)->count();
                    if($num<=0){
                        unset($arr[$k4]);
                    }
            }*/

            $arr1=$arr;
            $arr2=$arr;
            $arr3=$arr;
            if(isset($arr['Completed'])){
                $arr['Completed']=(int)$arr['Completed'];
            }
            $res = Db::name('pj_contract_review')->wherein('id',$data['arr'])->update($arr);
            $Filing_Code=Db::name('pj_contract_review')->wherein('id',$data['arr'])->field('Filing_Code')->select();
            // 同步更新 项目描述表复制上面的
            $f = ['Pre_Formatter','Translator','Reviser','Post_Formatter','Language','File_Type','File_Category','Format_Difficulty','Translation_Difficulty','Comment',
                'Pre_Format_Delivery_Time','Translation_Delivery_Time',
                'Revision_Delivery_Time','Post_Format_Delivery_Time'];
            foreach ($field as $key=>$val){
                if(!in_array($val, $f)){
                    unset($arr[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                $res=   Db::name('pj_project_profile')
                    ->where('Filing_Code', $v['Filing_Code'])
                    ->update($arr);
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

            // 同步更新 项目数据库表 相关信息
            $d = ['Translator','Reviser','Pre_Formatter','Post_Formatter','Language','File_Type','File_Category',
                'Completed','Delivered_or_Not','File_Category', 'PA'];
            foreach ($field as $key=>$val){
                if(!in_array($val, $d)) {
                    unset($arr1[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                Db::name('pj_project_database')
                    ->where('Filing_Code', $v['Filing_Code'])
                    ->update($arr1);
            }

            //同步更新是否交稿到结算管理
            $deliver = ['Delivered_or_Not','Translation_Difficulty','Format_Difficulty'];
            //$deliver = ['Delivered_or_Not'];
            foreach ($field as $key=>$val){
                if(!in_array($val, $deliver)) {
                    unset($arr2[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                Db::name('mk_invoicing')
                    ->where('Filing_Code',$v['Filing_Code'])
                    ->update($arr2);
            }

            //同步更新翻译难度/排版难度到来稿确认表
            $difficulty = ['Translation_Difficulty','Format_Difficulty'];
            foreach ($field as $key=>$val){
                if(!in_array($val, $difficulty)) {
                    unset($arr3[$val]);
                }
            }
            foreach ($Filing_Code as $k=>$v){
                Db::name('mk_feseability')
                    ->where('Filing_Code',$v['Filing_Code'])
                    ->update($arr3);
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

    public function import()
    {

        try{
            require '../extend/PHPExcel/PHPExcel.php';

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
                    $res_arr[$row-2]['Filing_Code']  = trim($sheet->getCell("A".$row)->getValue());
                    $res_arr[$row-2]['Job_Name']  = trim($sheet->getCell("B".$row)->getValue());
                    $res_arr[$row-2]['Company_Name']  = trim($sheet->getCell("C".$row)->getValue());
                    $res_arr[$row-2]['Pages']  = trim($sheet->getCell("D".$row)->getValue());
                    $res_arr[$row-2]['Source_Text_Word_Count']  = trim($sheet->getCell("E".$row)->getValue());
                    $res_arr[$row-2]['File_Type']  = trim($sheet->getCell("F".$row)->getValue());
                    $res_arr[$row-2]['Service']  = trim($sheet->getCell("G".$row)->getValue());
                    $res_arr[$row-2]['File_Category']  = trim($sheet->getCell("H".$row)->getValue());
                    $res_arr[$row-2]['Language']  = trim($sheet->getCell("I".$row)->getValue());
                    $res_arr[$row-2]['Format_Difficulty']  = trim($sheet->getCell("J".$row)->getValue());
                    $res_arr[$row-2]['Translation_Difficulty']  = trim($sheet->getCell("K".$row)->getValue());
                    $res_arr[$row-2]['Translator']  = trim($sheet->getCell("L".$row)->getValue());
                    $res_arr[$row-2]['Translation_Start_Time']  = trim($sheet->getCell("M".$row)->getValue());
                    $res_arr[$row-2]['Translation_Delivery_Time']  = trim($sheet->getCell("N".$row)->getValue());
                    $res_arr[$row-2]['Reviser']  = trim($sheet->getCell("O".$row)->getValue());

                    /*if($sheet->getCell("O".$row)->getValue()==''){
                        $res_arr[$row-2]['Delivery_Date_Expected']  ='';
                    }else{
                        $res_arr[$row-2]['Delivery_Date_Expected']  =date('Y-m-d H:i',strtotime(gmdate('Y-m-d H:i',\PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("O".$row)->getValue()))));
                    }*/
                    $res_arr[$row-2]['Revision_Start_Time']  = trim($sheet->getCell("P".$row)->getValue());
                    $res_arr[$row-2]['Revision_Delivery_Time']  = trim($sheet->getCell("Q".$row)->getValue());
                    $res_arr[$row-2]['Pre_Formatter']  = trim($sheet->getCell("R".$row)->getValue());
                    $res_arr[$row-2]['Pre_Format_Delivery_Time']  = trim($sheet->getCell("S".$row)->getValue());
                    $res_arr[$row-2]['Post_Formatter']  = trim($sheet->getCell("T".$row)->getValue());
                    $res_arr[$row-2]['Post_Format_Delivery_Time']  = trim($sheet->getCell("U".$row)->getValue());

                    if($sheet->getCell("V".$row)->getValue()==''){
                        $res_arr[$row-2]['Delivery_Date_Expected']  ='';
                    }else{
                        $res_arr[$row-2]['Delivery_Date_Expected']  =date('Y-m-d H:i',strtotime(gmdate('Y-m-d H:i',\PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("V".$row)->getValue()))));
                    }
                    $res_arr[$row-2]['Completed']  = trim($sheet->getCell("W".$row)->getValue());
                    $res_arr[$row-2]['Delivered_or_Not']  = trim($sheet->getCell("X".$row)->getValue());
                    $res_arr[$row-2]['Attention']  = trim($sheet->getCell("Y".$row)->getValue());
                    $res_arr[$row-2]['Customer_Requirements']  = trim($sheet->getCell("Z".$row)->getValue());
                    $res_arr[$row-2]['External_Reference_File']  = trim($sheet->getCell("AA".$row)->getValue());
                    $res_arr[$row-2]['First_Cooperation']  = trim($sheet->getCell("AB".$row)->getValue());
                    $res_arr[$row-2]['Quality_Requirements']  = trim($sheet->getCell("AC".$row)->getValue());
                    $res_arr[$row-2]['PA']  = trim($sheet->getCell("AD".$row)->getValue());
                    $res_arr[$row-2]['PM']  = trim($sheet->getCell("AE".$row)->getValue());
                    $res_arr[$row-2]['Sales']  = trim($sheet->getCell("AF".$row)->getValue());
                    $res_arr[$row-2]['Approval_Project_Manager']  = trim($sheet->getCell("AG".$row)->getValue());
                    $res_arr[$row-2]['Approval_General_Manager']  = trim($sheet->getCell("AH".$row)->getValue());
                    $res_arr[$row-2]['Filled_by']  = trim($sheet->getCell("AI".$row)->getValue());
                    $res_arr[$row-2]['Date']  = trim($sheet->getCell("AJ".$row)->getValue());
                    $res_arr[$row-2]['Comment']  = trim($sheet->getCell("AK".$row)->getValue());

                }

                Db::name('pj_contract_review')->insertAll($res_arr);

            }

        }catch(\Exception $e){
            $this->error('执行错误',$e->getMessage());
        }
        return json(['code'=>1,'msg'=>'导入成功']);

    }

    //项目汇总批量拆分成多条项目描述
    public function split($c_id){
        // 文件库
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('Filled_by', session('administrator')['name'])
            ->where('delete_time',0)->order('id desc')->select();
        // 返回视图
        return view('',['c_id'=>$c_id,'text_list'=>$text_list]);
    }

    public function add_split(Request $request){
        // 获取提交的数据
        $data = $request->post();
        // 用户
        $name = session('administrator')['name'];
        //通过id查询项目汇总表信息
        $xmhz = Db::name('pj_contract_review')->where('id',$data['c_id'])->find();
        $len = $data['split_num'];
        if($len>=1){
            for($i=0;$i<$len;$i++){
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

            //同步项目名称到术语数据记录表
            $where3 = [
                'Project_Name' => $data['Project_Name'],
                'delete_time' => 0,
            ];
            $name = session('administrator')['name'];
            $updata3 = [
                'Project_Name' => $data['Project_Name'],
                'Filled_by' => $name
            ];
            //截取项目名称日期
            $date = substr($data['Project_Name'],0,9);

            if($date >= 20220901){
                $pj = Db::name('pj_term_record')->where($where3)->find();
                if(!$pj){
                    Db::name('pj_term_record')->insert($updata3);
                }
            }


            // 返回操作结果
            return json(['msg'=>'拆分成功']);
        }

    }


    //批量添加项目描述
    public function batch_ms($id){
        $id_arr = explode(',' , $id);

        $id_arr = array_reverse($id_arr);
        // 用户
        $name = session('administrator')['name'];
        foreach($id_arr as $key=>$v){
            //通过id查询项目汇总表信息
            $xmhz = Db::name('pj_contract_review')->where('id',$v)->find();
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