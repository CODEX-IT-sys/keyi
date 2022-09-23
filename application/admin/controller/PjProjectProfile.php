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
                case 'trre_range':
                    $colsData[$k]['width'] = 150;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'lang_style':
                    $colsData[$k]['width'] = 150;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'format':
                    $colsData[$k]['width'] = 150;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'deliverables':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'other_remark':
                    $colsData[$k]['width'] = 180;
                    $colsData[$k]['sort'] = 'true';
                case 'Spot_Check':
                    $colsData[$k]['width'] = 120;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'QCR_Feedback':
                    $colsData[$k]['width'] = 120;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'Format_QL':
                    $colsData[$k]['width'] = 130;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'Translation_QL':
                    $colsData[$k]['width'] = 130;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'Proposal':
                    $colsData[$k]['width'] = 120;
                    $colsData[$k]['sort'] = 'true';
                    break;
                case 'Stage':
                    $colsData[$k]['width'] = 120;
                    $colsData[$k]['sort'] = 'true';
                    $colsData[$k]['style'] = 'color: red';
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
                'Field'=>'File_Category',
                'Comment'=>'文件分类'
            ],
            [
            'Field'=>'whether_template',
            'Comment'=>'是否可作为类型库的模板文件'
            ],
            [
                'Field'=>'Format_Difficulty',
                'Comment'=>'排版难易程度'
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
                'Field'=>'Brand_and_Model',
                'Comment'=>'品牌型号'
            ],
            [
                'Field'=>'Industry_Field',
                'Comment'=>'应用领域'
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
            [
                'Field' => 'PA',
                'Comment' => '项目组长'
            ],
            [
                'Field' => 'Spot_Check',
                'Comment' => 'QCR状态'
            ],
            [
                'Field' => 'QCR_Feedback',
                'Comment' => 'QCR反馈'
            ],
            [
                'Field' => 'Revise_Style',
                'Comment' => '校对类型'
            ],
        ];

        if($request->has('search_type')){
            $data= $request->only(['search_type']);
            $search_type=$data ["search_type"];
        }

        //判断是否选择简洁模板
        $simple = $request->param('simple');
        if($simple == 3){
            session('simple',3);
        }
        $tag = session('simple');

        if($tag == 3){

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
                        $colsData[$k]['hide']=true;
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
                    case 'Language':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'Product_Involved':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'File_Type':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'File_Category':
                        $colsData[$k]['hide']=true;
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
                    case 'Format_Difficulty':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'Translation_Difficulty':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'One_Hundred_Percent_Repeated':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'Ninety_Five_to_Ninety_Nine_Percent_Repeated':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'Total_Repetition_Rate':
                        $colsData[$k]['hide']=true;
                        break;
                    case 'Excluding_Words':
                        $colsData[$k]['hide']=true;
                        break;

                    case 'Pre_Format_Delivery_Time':
                        $colsData[$k]['hide']=true;
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
                        $colsData[$k]['hide']=true;
                        $colsData[$k]['sort']='true';
                        $colsData[$k]['width']=100;
                        break;
                    case 'Post_Formatter':
                        $colsData[$k]['sort']='true';
                        $colsData[$k]['width']=100;
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
                    case 'Spot_Check':
                        $colsData[$k]['width'] = 120;
                        $colsData[$k]['sort'] = 'true';
                    case 'QCR_Feedback':
                        $colsData[$k]['width'] = 120;
                        $colsData[$k]['sort'] = 'true';
                        break;
                    case 'Format_QL':
                        $colsData[$k]['width'] = 120;
                        $colsData[$k]['sort'] = 'true';
                        break;
                    case 'Translation_QL':
                        $colsData[$k]['width'] = 120;
                        $colsData[$k]['sort'] = 'true';
                        break;
                    case 'Proposal':
                        $colsData[$k]['width'] = 120;
                        $colsData[$k]['sort'] = 'true';
                        break;
                    default:
                        $colsData[$k]['width']=80;

                }

            }
        }
        $job_id = session('administrator')['job_id'];
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),
                'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword,'editor'=>$edit,
                'search_type'=>$search_type,'job_id'=>$job_id
            ]);
        }
        if($keyword == '待翻译QCR'){
            $keyword = 9;
        }elseif($keyword == '待排版QCR'){
            $keyword = 8;
        }elseif($keyword == '翻译QCR'){
            $keyword = 7;
        }elseif($keyword == '排版QCR'){
            $keyword = 6;
        }elseif($keyword == '翻译QCR,排版QCR'){
            $keyword = 5;
        }
        // 调用模型获取列表
        $list = PjProjectProfileModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    public function zhikong(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50){


        $colsData[0] = [
            'Field' => 'Filing_Code',
            'Comment' => '文件编号',
        ];
        $colsData[1] = [
            'Field' => 'Job_Name',
            'Comment' => '文件名称',
        ];
        $colsData[2] = [
            'Field' => 'Pages',
            'Comment' => '页数',
        ];
        $colsData[3] = [
            'Field' => 'Pre_Formatter',
            'Comment' => '预排版人员',
        ];
        $colsData[4] = [
            'Field' => 'Translator',
            'Comment' => '翻译人员',
        ];
        $colsData[5] = [
            'Field' => 'Reviser',
            'Comment' => '校对人员',
        ];
        $colsData[6] = [
            'Field' => 'Post_Formatter',
            'Comment' => '后排版人员',
        ];
        $colsData[7] = [
            'Field' => 'Completed',
            'Comment' => '交付日期',
        ];
        $colsData[8] = [
            'Field' => 'PA',
            'Comment' => '项目组长',
        ];

        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'field'=>$field, 'keyword'=>$keyword,'select_field'=>$colsData, 'colsData' => json_encode($colsData),
                'search_type'=>$search_type
            ]);
        }



        // 字段不为空
        if($search_type == 'and'){

            //$map['b.Completed'] = ['like',"%2022%"];
            // 如果有搜索类型，添加查询条件
            $field_arr = explode(',' , $field);//字段数组
            $keyword_arr = explode(',' , $keyword);//关键词数组

            //多字段 且 查询
            foreach ($field_arr as $k => $v){
                foreach ($keyword_arr as $key => $val){
                    if($k == $key){
                        if($v != 'PA' && $v != 'Completed'){
                            $map[] = ['a.'.$v,'LIKE',"%".$val."%"];

                        }else{
                            $map[] = ['b.'.$v,'LIKE',"%".$val."%"];
                        }

                    }
                }
            }

            $list = Db::table('ky_pj_project_profile')
                ->alias('a')
                ->leftjoin('ky_pj_contract_review b','a.Filing_code = b.Filing_code')
                ->where($map)
                ->field(['a.Filing_Code','a.Job_Name','a.Company_Name','a.Pages','a.Source_Text_Word_Count','a.Language','a.Product_Involved',
                    'a.File_Usage_and_Linguistic_Specification','a.File_Type','a.Format_Difficulty','a.Translation_Difficulty','a.One_Hundred_Percent_Repeated',
                    'a.Ninety_Five_to_Ninety_Nine_Percent_Repeated','a.Total_Repetition_Rate','a.Actual_Source_Text_Count','a.Pre_Formatter','a.Translator',
                    'a.Reviser','a.Post_Formatter','b.Completed','b.Delivered_or_Not','b.Attention','b.Customer_Requirements','b.External_Reference_File','b.First_Cooperation','b.PA'])
                ->paginate($limit);


        }else{

            if(empty($field)){
                $list = Db::table('ky_pj_project_profile')
                    ->alias('a')
                    ->leftjoin('ky_pj_contract_review b','a.Filing_code = b.Filing_code')
                    ->field(['a.Filing_Code','a.Job_Name','a.Company_Name','a.Pages','a.Source_Text_Word_Count','a.Language','a.Product_Involved',
                        'a.File_Usage_and_Linguistic_Specification','a.File_Type','a.Format_Difficulty','a.Translation_Difficulty','a.One_Hundred_Percent_Repeated',
                        'a.Ninety_Five_to_Ninety_Nine_Percent_Repeated','a.Total_Repetition_Rate','a.Actual_Source_Text_Count','a.Pre_Formatter','a.Translator',
                        'a.Reviser','a.Post_Formatter','b.Completed','b.Delivered_or_Not','b.Attention','b.Customer_Requirements','b.External_Reference_File','b.First_Cooperation','b.PA'])
                    ->paginate($limit);
            }else{
                if($field != 'PA' && $field != 'Completed'){

                    $list = Db::table('ky_pj_project_profile')
                        ->alias('a')
                        ->leftjoin('ky_pj_contract_review b','a.Filing_code = b.Filing_code')
                        ->where('a.'.$field,'like','%'.$keyword.'%')

                        ->field(['a.Filing_Code','a.Job_Name','a.Company_Name','a.Pages','a.Source_Text_Word_Count','a.Language','a.Product_Involved',
                            'a.File_Usage_and_Linguistic_Specification','a.File_Type','a.Format_Difficulty','a.Translation_Difficulty','a.One_Hundred_Percent_Repeated',
                            'a.Ninety_Five_to_Ninety_Nine_Percent_Repeated','a.Total_Repetition_Rate','a.Actual_Source_Text_Count','a.Pre_Formatter','a.Translator',
                            'a.Reviser','a.Post_Formatter','b.Completed','b.Delivered_or_Not','b.Attention','b.Customer_Requirements','b.External_Reference_File','b.First_Cooperation','b.PA'])
                        ->paginate($limit);

                }else{
                    $list = Db::table('ky_pj_project_profile')
                        ->alias('a')
                        ->leftjoin('ky_pj_contract_review b','a.Filing_code = b.Filing_code')
                        ->where('b.'.$field,'like','%'.$keyword.'%')
                        ->field(['a.Filing_Code','a.Job_Name','a.Company_Name','a.Pages','a.Source_Text_Word_Count','a.Language','a.Product_Involved',
                            'a.File_Usage_and_Linguistic_Specification','a.File_Type','a.Format_Difficulty','a.Translation_Difficulty','a.One_Hundred_Percent_Repeated',
                            'a.Ninety_Five_to_Ninety_Nine_Percent_Repeated','a.Total_Repetition_Rate','a.Actual_Source_Text_Count','a.Pre_Formatter','a.Translator',
                            'a.Reviser','a.Post_Formatter','b.Completed','b.Delivered_or_Not','b.Attention','b.Customer_Requirements','b.External_Reference_File','b.First_Cooperation','b.PA'])
                        ->paginate($limit);
                }

            }
        }




        /* return [
           'code'=>0,
             'message'=> '成功',
             'data' => $list,
             'count' => 500
         ];*/
        // 返回数据
        return json(generate_layui_table_data($list));

    }

    public function sessionOut(){
        session('simple',null);
        $this->redirect('index');
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
    public function condition_zk()
    {
        // 数据库表字段集
        //$colsData = getAllField('ky_pj_project_profile');
        $colsData[0] = [
            'Field' => 'Filing_Code',
            'Comment' => '文件编号',
        ];
        $colsData[1] = [
            'Field' => 'Job_Name',
            'Comment' => '文件名称',
        ];
        $colsData[2] = [
            'Field' => 'Pages',
            'Comment' => '页数',
        ];
        $colsData[3] = [
            'Field' => 'Pre_Formatter',
            'Comment' => '预排版人员',
        ];
        $colsData[4] = [
            'Field' => 'Translator',
            'Comment' => '翻译人员',
        ];
        $colsData[5] = [
            'Field' => 'Reviser',
            'Comment' => '校对人员',
        ];
        $colsData[6] = [
            'Field' => 'Post_Formatter',
            'Comment' => '后排版人员',
        ];
        $colsData[7] = [
            'Field' => 'Completed',
            'Comment' => '交付日期',
        ];
        $colsData[8] = [
            'Field' => 'PA',
            'Comment' => '项目组长',
        ];
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
            ->where(['delete_time'=>0])->select();
        $tr = array_merge($tr, $na);

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['delete_time'=>0])->select();
        $re = array_merge($re, $na);

        // 预排
        $yp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['delete_time'=>0])->select();
        $yp = array_merge($yp, $na);

        // 后排
        $hp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['delete_time'=>0])->select();
        $hp = array_merge($hp, $na);

        // 项目助理
        $pa = Admin::field('name')->where(['job_id'=> 7])->select();

        // 文件库
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('Filled_by', session('administrator')['name'])
            ->where('delete_time',0)->order('id desc')->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();

        // 直接返回视图
        return view('form-project_profile', [
            'file_code'=>$file_code,'yy'=>$yy, 'pb'=>$pb, 'fy'=>$fy, 'File_Type'=>$File_Type,
            'document_type'=>json_encode($document_type), 'text_list'=>$text_list,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
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
            ->where(['delete_time'=>0])->select();
        $tr = array_merge($tr, $na);
        foreach ($tr as $k => $v){
            if(in_array($v['name'],$tr_arr)){
                $tr[$k]['selected'] = true;
            }
        }

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,8,4,15,6])
            ->where(['delete_time'=>0])->select();
        $re = array_merge($re, $na);
        foreach ($re as $k => $v){
            if(in_array($v['name'],$re_arr)){
                $re[$k]['selected'] = true;
            }
        }

        // 预排
        $yp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['delete_time'=>0])->select();
        $yp = array_merge($yp, $na);
        foreach ($yp as $k => $v){
            if(in_array($v['name'],$yp_arr)){
                $yp[$k]['selected'] = true;
            }
        }

        // 后排
        $hp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,12,13,5])
            ->where(['delete_time'=>0])->select();
        $hp = array_merge($hp, $na);
        foreach ($hp as $k => $v){
            if(in_array($v['name'],$hp_arr)){
                $hp[$k]['selected'] = true;
            }
        }

        // 项目助理
        $pa = Admin::field('name')->where('job_id', 7)->where('status', 0)->select();

        //翻译校对范围
        $trre_range = Db::name('xt_dict')->where('c_id',17)->select();

        //语言风格
        $language = Db::name('xt_dict')->where('c_id',18)->select();

        //排版
        $format = Db::name('xt_dict')->where('c_id',19)->select();

        //提交内容
        $deliver = Db::name('xt_dict')->where('c_id',20)->select();

        // 文件库
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('Filled_by', session('administrator')['name'])
            ->where('delete_time',0)->order('id desc')->select();

        return view('form-project_profile-view', [
            'info'=>$res,'yy'=>$yy, 'pb'=>$pb, 'fy'=>$fy, 'File_Type'=>$File_Type,
            'document_type'=>json_encode($document_type), 'text_list'=>$text_list,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa,
            'trre_range'=>$trre_range,'language'=>$language,'format'=>$format,'deliver'=>$deliver,
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

        //同步校对类型到项目汇总和项目数据库
        if($data['Revise_Style']){
            //判断是否有同一个文件编号的记录存在
            $where1 = [
                'Filing_Code'=>$data['Filing_Code'],
                'delete_time' => 0,
            ];
            $re = Db::name('pj_project_profile')->where($where1)->select();
            $re_len = count($re);
            if($re_len == 1){
                $up_data = [
                    'Revise_Style' => $data['Revise_Style']
                ];

            }else{
                $ms = Db::name('pj_project_profile')->where($where1)->select();
                $revise = [];
                foreach($ms as $key=>$val){
                    $revise[] = $val['Revise_Style'];
                }

                $revise = array_unique($revise);
                $len = count($revise);
                if($len == 1){
                    $up_data = [
                        'Revise_Style' => $data['Revise_Style']
                    ];
                }else{
                    if(in_array('抽样标黄校对',$revise)){
                        $up_data = [
                            'Revise_Style' => '抽样标黄校对'
                        ];
                    }else{
                        if(in_array('标黄校对',$revise)){
                            $up_data = [
                                'Revise_Style' => '标黄校对'
                            ];
                        }else{
                            $up_data = [
                                'Revise_Style' => '无校对'
                            ];
                        }
                    }
                }
            }
            $where = [
                'Filing_Code' => $data['Filing_Code'],
            ];

            Db::name('pj_contract_review')->where($where)->update($up_data);
            Db::name('pj_project_database')->where($where)->update($up_data);

        }
        //项目数据库同步 是否可作为类型库的模板文件 品牌型号 应用领域
        $up_data2 = [
            'whether_template' => $data['whether_template'],
            'Brand_and_Model' => $data['Brand_and_Model'],
            'Industry_Field' => $data['Industry_Field'],
        ];
        Db::name('pj_project_database')->where('Filing_Code',$data['Filing_Code'])->update($up_data2);

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

        $up_database = [
            'Product_Involved'=>$data['Product_Involved'],
            'whether_template'=>$data['whether_template'],
            'Brand_and_Model' => $data['Brand_and_Model'],
            'Industry_Field' => $data['Industry_Field'],
        ];
        Db::name('pj_project_database')->where('Filing_Code',$data['Filing_Code'])->update($up_database);
        Db::name('pj_project_profile')->where('Filing_Code',$data['Filing_Code'])->update(['Product_Involved'=>$data['Product_Involved']]);
        PjProjectProfileModel::update($data);
        //同步校对类型到项目汇总和项目数据库
        if($data['Revise_Style']){
            //判断是否有同一个文件编号的记录存在
            $where1 = [
                'Filing_Code'=>$data['Filing_Code'],
                'delete_time' => 0,
            ];
            $re = Db::name('pj_project_profile')->where($where1)->select();
            $re_len = count($re);
            if($re_len == 1){
                $up_data = [
                    'Revise_Style' => $data['Revise_Style']
                ];

            }else{
                $ms = Db::name('pj_project_profile')->where($where1)->select();
                $revise = [];
                foreach($ms as $key=>$val){
                    $revise[] = $val['Revise_Style'];
                }

                $revise = array_unique($revise);
                $len = count($revise);
                if($len == 1){
                    $up_data = [
                        'Revise_Style' => $data['Revise_Style']
                    ];
                }else{
                    if(in_array('抽样标黄校对',$revise)){
                        $up_data = [
                            'Revise_Style' => '抽样标黄校对'
                        ];
                    }else{
                        if(in_array('标黄校对',$revise)){
                            $up_data = [
                                'Revise_Style' => '标黄校对'
                            ];
                        }else{
                            $up_data = [
                                'Revise_Style' => '无校对'
                            ];
                        }
                    }
                }
            }
            $where = [
                'Filing_Code' => $data['Filing_Code'],
            ];
            Db::name('pj_contract_review')->where($where)->update($up_data);
            Db::name('pj_project_database')->where($where)->update($up_data);

        }


        //同步项目名称到术语数据记录表
        if($data['Project_Name']){
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

        }

        //同步产品名称
        if($data['Product_Involved']){
            $where3 = [
                'Project_Name' => $data['Project_Name'],
                'delete_time' => 0,
            ];

            Db::name('pj_term_record')->where($where3)->update(['Product_Name'=>$data['Product_Involved']]);
        }
        //同步最终交付时间
        if($data['Final_Delivery_Time']){
            $where3 = [
                'Project_Name' => $data['Project_Name'],
                'delete_time' => 0,
            ];
            //找到最晚的时间
            $ms = Db::name('pj_project_profile')->where($where3)->select();
            $arr = [];
            if($ms){
                foreach($ms as $key=>$val){
                    $arr[] = strtotime($val['Final_Delivery_Time']);
                }

                $d_time = max($arr);

                if($d_time){
                    $d_time = date('Y-m-d H:i',$d_time);
                    $pj = Db::name('pj_term_record')->where($where3)->update(['Final_Delivery_Time'=>$d_time]);
                }
            }

        }

        //同步QCR反馈到质量抽查表
        if($data['QCR_Feedback']){
            //判断是否有同一个文件编号的记录存在
            $where3 = [
                'Filing_Code'=>$data['Filing_Code'],
                'Job_Name' => $data['Job_Name'],
                'delete_time' => 0,
            ];
            $check = Db::name('pj_check')->where($where3)->select();
            foreach($check as $k2=>$v2){
                $update3 = [
                    'QCR_Feedback' => $data['QCR_Feedback']
                ];
                $re = Db::name('pj_check')->where('id',$v2['id'])->update($update3);
            }

        }
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
        $tr = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,12,13,8,4,15,6,19])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $tr = array_merge($tr, $na);
        foreach ($tr as $k => $v){
            if(in_array($v['name'],$tr_arr)){
                $tr[$k]['selected'] = true;
            }
        }

        // 校对
        $re = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [10,11,12,13,8,4,15,6])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $re = array_merge($re, $na);
        foreach ($re as $k => $v){
            if(in_array($v['name'],$re_arr)){
                $re[$k]['selected'] = true;
            }
        }

        // 预排
        $yp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,10,11,12,13,5])
            ->where(['status'=> 0, 'delete_time'=>0])->select();
        $yp = array_merge($yp, $na);
        foreach ($yp as $k => $v){
            if(in_array($v['name'],$yp_arr)){
                $yp[$k]['selected'] = true;
            }
        }

        // 后排
        $hp = Db::name('admin')->field('id as value, name')->where('job_id', 'in', [19,10,11,12,13,5])
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
                        if($v == 'Spot_Check'){
                            if($v1 == '待翻译QCR'){
                                $arr[$v]= 9;
                            }elseif($v1 == '待排版QCR'){
                                $arr[$v]= 8;
                            }else{
                                $arr[$v]= 1;
                            }
                        }else{
                            $arr[$v]= $v1;
                        }

                    }
                }
            }
            $res = Db::name('pj_project_profile')->wherein('id',$data['arr'])->update($arr);

            //如果涉及到校对类型 需要同步
            if(in_array('Revise_Style',$field)){
                foreach($data['arr'] as $k2=>$v2){
                    $xm = Db::name('pj_project_profile')->where('id',$v2)->field('Filing_Code,Revise_Style')->find();
                    //同步校对类型到项目汇总和项目数据库

                    //判断有同一个文件编号的几条记录存在
                    $where1 = [
                        'Filing_Code'=>$xm['Filing_Code'],
                        'delete_time' => 0,
                    ];
                    $re = Db::name('pj_project_profile')->where($where1)->select();
                    $re_len = count($re);
                    if($re_len == 1){
                        $up_data = [
                            'Revise_Style' => $xm['Revise_Style']
                        ];

                    }else{
                        $ms = Db::name('pj_project_profile')->where($where1)->select();
                        $revise = [];
                        foreach($ms as $key=>$val){
                            $revise[] = $val['Revise_Style'];
                        }

                        $revise = array_unique($revise);
                        $len = count($revise);
                        if($len == 1){
                            $up_data = [
                                'Revise_Style' => $xm['Revise_Style']
                            ];
                        }else{
                            if(in_array('抽样标黄校对',$revise)){
                                $up_data = [
                                    'Revise_Style' => '抽样标黄校对'
                                ];
                            }else{
                                if(in_array('标黄校对',$revise)){
                                    $up_data = [
                                        'Revise_Style' => '标黄校对'
                                    ];
                                }else{
                                    $up_data = [
                                        'Revise_Style' => '无校对'
                                    ];
                                }
                            }
                        }
                    }
                    $where = [
                        'Filing_Code' => $xm['Filing_Code'],
                    ];
                    Db::name('pj_contract_review')->where($where)->update($up_data);
                    Db::name('pj_project_database')->where($where)->update($up_data);


                }
            }


            //同步QCR反馈到质量抽查表
            if(in_array('QCR_Feedback',$field)){
                foreach($data['arr'] as $k3=>$v3) {
                    $xm2 = Db::name('pj_project_profile')->where('id', $v3)->field('Filing_Code,Job_Name,QCR_Feedback')->find();
                    $update_data = [
                        'QCR_Feedback' => $xm2['QCR_Feedback']
                    ];
                    $where3 = [
                        'Filing_Code' => $xm2['Filing_Code'],
                        'Job_Name' => $xm2['Job_Name']
                    ];

                    Db::name('pj_check')->where($where3)->update($update_data);
                }
            }

            //如果存在 是否可作为类型库的模板文件，需要同步到项目数据库
            if(in_array('whether_template',$field)){
                foreach($data['arr'] as $k4=>$v4) {
                    $xm2 = Db::name('pj_project_profile')->where('id', $v4)->field('Filing_Code,Job_Name,whether_template')->find();
                    $update_data = [
                        'whether_template' => $xm2['whether_template']
                    ];
                    $where4 = [
                        'Filing_Code' => $xm2['Filing_Code'],
                        'Job_Name' => $xm2['Job_Name']
                    ];

                    Db::name('pj_project_database')->where($where4)->update($update_data);
                }
            }

            //如果存在 品牌型号，需要同步到项目数据库
            if(in_array('Brand_and_Model',$field)){
                foreach($data['arr'] as $k4=>$v4) {
                    $xm2 = Db::name('pj_project_profile')->where('id', $v4)->field('Filing_Code,Job_Name,Brand_and_Model')->find();
                    $update_data = [
                        'Brand_and_Model' => $xm2['Brand_and_Model']
                    ];
                    $where4 = [
                        'Filing_Code' => $xm2['Filing_Code'],
                        'Job_Name' => $xm2['Job_Name']
                    ];

                    Db::name('pj_project_database')->where($where4)->update($update_data);
                }
            }

            //如果存在 应用领域，需要同步到项目数据库
            if(in_array('Industry_Field',$field)){
                foreach($data['arr'] as $k4=>$v4) {
                    $xm2 = Db::name('pj_project_profile')->where('id', $v4)->field('Filing_Code,Job_Name,Industry_Field')->find();
                    $update_data = [
                        'Industry_Field' => $xm2['Industry_Field']
                    ];
                    $where4 = [
                        'Filing_Code' => $xm2['Filing_Code'],
                        'Job_Name' => $xm2['Job_Name']
                    ];

                    Db::name('pj_project_database')->where($where4)->update($update_data);
                }
            }

            //同步最终交付时间
            if(in_array('Final_Delivery_Time',$field)){

                foreach($data['arr'] as $k4=>$v4) {
                    $xm2 = Db::name('pj_project_profile')->where('id', $v4)->field('Filing_Code,Job_Name,Project_Name,Final_Delivery_Time')->find();

                    $where5 = [
                        'Project_Name' => $xm2['Project_Name'],
                        'delete_time' => 0,
                    ];
                    //找到最晚的时间
                    $ms = Db::name('pj_project_profile')->where($where5)->select();
                    $arr = [];
                    if($ms){
                        foreach($ms as $key=>$val){
                            $arr[] = strtotime($val['Final_Delivery_Time']);
                        }
                        $d_time = max($arr);

                        if($d_time){
                            $d_time = date('Y-m-d H:i',$d_time);
                            $pj = Db::name('pj_term_record')->where($where5)->update(['Final_Delivery_Time'=>$d_time]);
                        }
                    }


                }


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

    //文件名编号效验
    public function inspection(Request $request)
    {
        try {
            $data=$request->param();
            $res = Db::name('pj_project_profile')->wherein('Filing_Code',$data['Filing_Code'])->where('delete_time',0)->count();
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
                $highestRow = round(($highestRow-18)/13);
                for ($row = 1; $row <= $highestRow; $row++) {
                    $a = ($row-1)*13+3+16;
                    $d = ($row-1)*13+15+16;
                    $f1 = ($row-1)*13+4+16;
                    $f2 = ($row-1)*13+5+16;
                    $f3 = ($row-1)*13+6+16;
                    $f4 = ($row-1)*13+7+16;
                    $f5 = ($row-1)*13+8+16;
                    $res_arr[$row - 1]['name'] = trim($sheet->getCell("A" . $a)->getValue());
                    $res_arr[$row - 1]['Source_Text_Word_Count'] = trim($sheet->getCell("D" . $d)->getValue());
                    $f1 = trim($sheet->getCell("F" . $f1)->getValue());
                    $f2 = trim($sheet->getCell("F" . $f2)->getValue());
                    $f3 = trim($sheet->getCell("F" . $f3)->getValue());
                    $f4 = trim($sheet->getCell("F" . $f4)->getValue());
                    $f5 = trim($sheet->getCell("F" . $f5)->getValue());
                    $res_arr[$row - 1]['One_Hundred_Percent_Repeated'] = floatval($f1)+floatval($f2)+floatval($f3)+floatval($f4);

                    $res_arr[$row - 1]['Ninety_Five_to_Ninety_Nine_Percent_Repeated'] = $f5;
                    $res_arr[$row - 1]['Total_Repetition_Rate'] = floatval($f1)+floatval($f2)+floatval($f3)+floatval($f4)+floatval($f5);
                    $res_arr[$row - 1]['Actual_Source_Text_Count'] = round($res_arr[$row - 1]['Source_Text_Word_Count']*(1-$res_arr[$row - 1]['Total_Repetition_Rate']));

                }

                foreach($res_arr as $key=>$val){
                    $up_data = [
                        'Source_Text_Word_Count' => $val['Source_Text_Word_Count'],
                        'One_Hundred_Percent_Repeated' => $val['One_Hundred_Percent_Repeated']*100,
                        'Ninety_Five_to_Ninety_Nine_Percent_Repeated' => $val['Ninety_Five_to_Ninety_Nine_Percent_Repeated']*100,
                        'Total_Repetition_Rate' => $val['Total_Repetition_Rate']*100,
                        'Actual_Source_Text_Count' =>$val['Actual_Source_Text_Count'],
                    ];
                    Db::name('pj_project_profile')->where('Job_Name',$val['name'])->update($up_data);
                }

            }

        } catch (\Exception $e) {
            $this->error('执行错误', $e->getMessage());
        }
        return json(['code' => 1, 'msg' => '导入成功']);

    }


    public function spot_check(Request $request){

        $job_id = session('administrator')['job_id'];
        if(!in_array($job_id, [1,7])) {
            return json(['msg' => '你没有权限操作！']);
        }
        // 获取提交的数据
        $data = $request->post();

        if($data['cate'] == '翻译'){
            $cate = '9';
        }else if($data['cate'] == '排版'){
            $cate = '8';
        }else{
            $cate = '待QCR';
        }

        $up_data = [
            'Spot_Check' => $cate,
        ];
        $res = Db::name('pj_project_profile')->where('id',$data['c_id'])
            ->update($up_data);
        if($res){
            return json(['msg' => '操作成功']);
        }else{
            return json(['msg' => '执行失败']);
        }

    }

    //更改项目阶段
    public function change_stage(Request $request){
        // 获取提交的数据
        $data = $request->post();
        $cate = $data['cate'];
        $msg = '您没有权限更改阶段到'.$cate;
        $job_id = session('administrator')['job_id'];
        $username = session('administrator')['name'];

        /*$name = Db::name('xt_dict')->where('c_id',32)->field('cn_name')->select();
        $arr = array_column($name,'cn_name');
        $x_arr = ['闻心宇','张攀','PA12','王畅'];
        $arr = array_merge($arr, $x_arr);
        if(!in_array($username,$arr)){
            return json(['msg'=>'您的小组暂未开放该功能']);
        }*/


        if(!in_array($job_id,[1,7,8,10,11,12,13,23])){
            return json(['msg'=>'不是项目组成员，无法操作']);
        }

        if(in_array($username,['闻心宇','张攀'])){
            if($cate != 'QCR待修改' && $cate != '提交组长'){
                return json(['msg' => $msg]);
            }
        }else{
            //job_id 翻译人员：10，校对人员：11，预排人员：12，后排人员：13，项目组长：7
            if($job_id == 10){
                if($cate != '校对' && $cate != '后排' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

            if($job_id == 11){
                if($cate != '后排' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

            if($job_id == 12){
                if($cate != '翻译' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

            if($job_id == 13){
                if($cate != '定稿' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

        }



        $up_data = [
            'Stage' => $cate,
        ];
        $res = Db::name('pj_project_profile')->where('id',$data['c_id'])
            ->update($up_data);
        if($res){
            return json(['msg' => '操作成功']);
        }else{
            return json(['msg' => '执行失败']);
        }
    }
    //批量更改项目阶段
    public function batch_stage(Request $request){
        // 获取提交的数据
        $data = $request->post();
        $cate = $data['cate'];
        $id = $data['c_id'];
        $id_arr = explode(',', $id);
        $id_arr = array_reverse($id_arr);

        //权限验证
        $msg = '您没有权限更改阶段到'.$cate;
        $job_id = session('administrator')['job_id'];
        $username = session('administrator')['name'];

        /*$name = Db::name('xt_dict')->where('c_id',32)->field('cn_name')->select();
        $arr = array_column($name,'cn_name');
        $x_arr = ['闻心宇','张攀','PA12','王畅'];
        $arr = array_merge($arr, $x_arr);
        if(!in_array($username,$arr)){
            return json(['msg'=>'您的小组暂未开放该功能']);
        }*/


        if(!in_array($job_id,[1,7,8,10,11,12,13,23])){
            return json(['msg'=>'不是项目组成员，无法操作']);
        }

        if(in_array($username,['闻心宇','张攀'])){
            if($cate != '校对修改' && $cate != '排版修改' && $cate != '提交组长'){
                return json(['msg' => $msg]);
            }
        }else{
            //job_id 翻译人员：10，校对人员：11，预排人员：12，后排人员：13，项目组长：7
            if($job_id == 10){
                if($cate != '校对' && $cate != '后排' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

            if($job_id == 11){
                if($cate != '后排' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

            if($job_id == 12){
                if($cate != '翻译' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

            if($job_id == 13){
                if($cate != '定稿' && $cate != 'QCR' && $cate != '提交组长'){
                    return json(['msg' => $msg]);
                }
            }

        }

        foreach ($id_arr as $key => $v) {
            $up_data = [
                'Stage' => $cate,
            ];
            $res = Db::name('pj_project_profile')->where('id',$v)
                ->update($up_data);
        }
        // 返回数据
        return json(['msg' => '操作成功']);
    }


    public function split($c_id)
    {
        // 文件库
        $text_list = Db::name('pj_project_profile_text')->field('id, Project_Name')
            ->where('Filled_by', session('administrator')['name'])
            ->where('delete_time', 0)->order('id desc')->select();
        // 返回视图
        return view('', ['c_id' => $c_id, 'text_list' => $text_list]);
    }

    public function stage($c_id)
    {
        //项目阶段
        $stage  = Db::name('xt_dict')->where('c_id',37)->field('cn_name,en_name')->select();

        // 返回视图
        return view('', ['c_id' => $c_id, 'stage' => $stage]);
    }

    public function pg_stage($c_id){
        //项目阶段
        $stage  = Db::name('xt_dict')->where('c_id',37)->field('cn_name,en_name')->select();

        // 返回视图
        return view('', ['c_id' => $c_id, 'stage' => $stage]);
    }
}