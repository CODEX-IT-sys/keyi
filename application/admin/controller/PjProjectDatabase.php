<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\common\model\Admin;
use app\facade\PjProjectDatabase as PjProjectDatabaseModel;
use think\Controller;
use think\Request;
use think\Db;

// 项目数据库 控制器
class PjProjectDatabase extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_project_database');

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
                    $colsData[$k]['hide']=true;
                    break;
                case 'Source_Text_Word_Count':
                    $colsData[$k]['width']=90;
                    $colsData[$k]['hide']=true;
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Pre_Format_Delivery_Time':
                    $colsData[$k]['width']=150;
                    break;
                case 'Post_Formatter':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Pre_Formatter':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Attention':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Product_Involved':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Product_Parts':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Brand_and_Model':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Industry_Field':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Date':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Completed':
                    $colsData[$k]['width']=100;
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
                    break;
                default:
                    $colsData[$k]['width']=80;

            }

        }
        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',7)->value('intro');
        $edit=[
            [
                'Field'=>'Update_Company_TM',
                'Comment'=>'是否更新公司主库'
            ],
            [
                'Field'=>'Update_File_TM',
                'Comment'=>'是否更新文件主库'
            ],
            [
                'Field'=>'Updating_Means',
                'Comment'=>'更新方式'
            ],
            [
                'Field'=>'Update_Time',
                'Comment'=>'更新时间'
            ],
            [
                'Field'=>'Product_Involved',
                'Comment'=>'涉及产品    '
            ],
            [
                'Field'=>'Product_Parts',
                'Comment'=>'产品部件'
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
        $list = PjProjectDatabaseModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_project_database');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 查询 可供预选的 编号值
        $file_code = Db::name('mk_feseability')->alias('f')
            ->join('pj_project_database c', 'f.Filing_Code = c.Filing_Code', 'left')
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
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 更新方式
		$up = Db::name('xt_dict')->where('c_id',11)->select();

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
        $pa = Admin::field('name')->where('job_id', 7)->where('status', 0)->select();

        //不符合项
        $ncr = [
            ['value'=>0, 'name'=>'无'],
            ['value'=>1, 'name'=>'TM01'],
            ['value'=>2, 'name'=>'TM02'],
            ['value'=>3, 'name'=>'TM03'],
            ['value'=>4, 'name'=>'TM04'],
            ['value'=>5, 'name'=>'TM05'],
            ['value'=>6, 'name'=>'TM06'],
            ['value'=>7, 'name'=>'TM07'],
            ['value'=>8, 'name'=>'TM08'],
            ['value'=>9, 'name'=>'TM09'],
            ['value'=>10, 'name'=>'TM10'],
            ['value'=>11, 'name'=>'TM11'],
            ['value'=>12, 'name'=>'TM12'],
            ['value'=>12, 'name'=>'TM13'],
        ];

        $typearr = [
            ['value'=>0, 'name'=>'No'],
            ['value'=>1, 'name'=>'No 文件未达更类型库标准'],
            ['value'=>2, 'name'=>'Yes MD/IVD综述资料'],
            ['value'=>3, 'name'=>'Yes MD/IVD证书声明'],
            ['value'=>4, 'name'=>'Yes MD/IVD上市后资料'],
            ['value'=>5, 'name'=>'Yes MD/IVD风险管理'],
            ['value'=>6, 'name'=>'Yes MD/IVD安全有效基本要求清单'],
            ['value'=>7, 'name'=>'Yes MD/IVD产品技术要求'],
            ['value'=>8, 'name'=>'Yes MD/IVD产品注册检验报告'],
            ['value'=>9, 'name'=>'Yes MD/IVD物理测试'],
            ['value'=>10, 'name'=>'Yes MD/IVD化学测试'],
            ['value'=>11, 'name'=>'Yes MD/IVD IEC电气测试'],
            ['value'=>12, 'name'=>'Yes MD/IVD辐射测试'],
            ['value'=>13, 'name'=>'Yes MD/IVD软件'],
            ['value'=>14, 'name'=>'Yes MD/IVD生物学评价'],
            ['value'=>15, 'name'=>'Yes MD/IVD灭菌测试'],
            ['value'=>16, 'name'=>'Yes MD/IVD非临床研究资料'],
            ['value'=>17, 'name'=>'Yes MD/IVD临床研究资料'],
            ['value'=>18, 'name'=>'Yes MD/IVD说明书、标签'],
            ['value'=>19, 'name'=>'Yes MD/IVD质量管理'],
            ['value'=>20, 'name'=>'Yes MD/IVD单据'],
            ['value'=>21, 'name'=>'Yes CTD-质量'],
            ['value'=>22, 'name'=>'Yes 药理学'],
            ['value'=>23, 'name'=>'Yes 药代动力学'],
            ['value'=>24, 'name'=>'Yes 毒理学'],
            ['value'=>25, 'name'=>'Yes CTD-非临床'],
            ['value'=>26, 'name'=>'Yes CTD-临床'],
            ['value'=>27, 'name'=>'Yes 上市后报告'],
            ['value'=>28, 'name'=>'Yes PBRER、PSUR、DSUR'],
            ['value'=>29, 'name'=>'Yes RMP'],
            ['value'=>30, 'name'=>'Yes SUSAR'],
            ['value'=>31, 'name'=>'Yes 检验报告COA'],
            ['value'=>32, 'name'=>'Yes 药品符合性声明'],
            ['value'=>33, 'name'=>'Yes SMF'],
            ['value'=>34, 'name'=>'Yes DMF'],
            ['value'=>35, 'name'=>'Yes 药品说明书'],
            ['value'=>36, 'name'=>'Yes 肿瘤基因检测报告（NGS）'],
            ['value'=>37, 'name'=>'Yes 境外生产药品注册-补充申请表'],
            ['value'=>38, 'name'=>'Yes 财报'],
            ['value'=>39, 'name'=>'Yes 研究者手册'],
            ['value'=>40, 'name'=>'Yes 注册标准'],
            ['value'=>41, 'name'=>'Yes EIR'],
            ['value'=>42, 'name'=>'Yes 证书声明'],
            ['value'=>43, 'name'=>'Yes 软件'],
            ['value'=>44, 'name'=>'Yes 标准A级'],
            ['value'=>45, 'name'=>'Yes 标准B级'],
            ['value'=>46, 'name'=>'Yes 法规A级'],
            ['value'=>47, 'name'=>'Yes 法规B级'],
            ['value'=>48, 'name'=>'Yes 合同协议/法律文件A级'],
            ['value'=>49, 'name'=>'Yes 合同协议/法律文件B级'],
            ['value'=>50, 'name'=>'Yes 专利A级'],
            ['value'=>51, 'name'=>'Yes 专利B级'],
            ['value'=>52, 'name'=>'Yes 医学文献/论文A级'],
            ['value'=>53, 'name'=>'Yes 医学文献/论文B级'],
            ['value'=>54, 'name'=>'Yes 化学品分析A级'],
            ['value'=>55, 'name'=>'Yes 化学品分析B级'],
            ['value'=>56, 'name'=>'Yes 发补/补正通知'],

            ['value'=>58, 'name'=>'Yes 小语种A级'],
            ['value'=>59, 'name'=>'Yes 小语种B级'],
            ['value'=>60, 'name'=>'Yes 个人A级'],
            ['value'=>61, 'name'=>'Yes 个人B级'],
        ];
        // 直接返回视图
        return view('form-project_database', [
            'file_code'=>$file_code,'File_Type'=>$File_Type,'document_type'=>json_encode($document_type),'up'=>$up, 'yy'=>$yy,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa,'typearr'=>$typearr,'ncr'=>json_encode($ncr),
        ]);
    }

    // 查看
    public function read($id)
    {

        // 查询信息
        $res = PjProjectDatabaseModel::get($id);

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

        // 更新方式
        $up = Db::name('xt_dict')->where('c_id',11)->select();


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

        // 直接返回视图
        return view('form-project_database-view',[
            'File_Type'=>$File_Type,'document_type'=>json_encode($document_type), 'yy'=>$yy, 'up'=>$up,'info'=>$res,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa
        ]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjProjectDatabaseModel::get($id);

        $fc_arr = explode(',', $res['File_Category']);

        $tr_arr = explode(',', $res['Translator']);
        $re_arr = explode(',', $res['Reviser']);
        $yp_arr = explode(',', $res['Pre_Formatter']);
        $hp_arr = explode(',', $res['Post_Formatter']);
        $ncr_arr = explode(',',$res['Non_Conformance']);
            //更新项目描述的涉及产品Product_Involved
        Db::name('pj_project_profile')->where('Filing_Code',$res['Filing_Code'])->update(['Product_Involved'=>$res['Product_Involved']]);
        // N/A 选项
        $na = [['value'=>0, 'name'=>'N/A']];

        //不符合项
        $ncr = [
            ['value'=>0, 'name'=>'无'],
            ['value'=>1, 'name'=>'TM01'],
            ['value'=>2, 'name'=>'TM02'],
            ['value'=>3, 'name'=>'TM03'],
            ['value'=>4, 'name'=>'TM04'],
            ['value'=>5, 'name'=>'TM05'],
            ['value'=>6, 'name'=>'TM06'],
            ['value'=>7, 'name'=>'TM07'],
            ['value'=>8, 'name'=>'TM08'],
            ['value'=>9, 'name'=>'TM09'],
            ['value'=>10, 'name'=>'TM10'],
            ['value'=>11, 'name'=>'TM11'],
            ['value'=>12, 'name'=>'TM12'],
            ['value'=>12, 'name'=>'TM13'],
        ];
        foreach ($ncr as $k => $v){
            if(in_array($v['name'],$ncr_arr)){
                $ncr[$k]['selected'] = true;
            }
        }

        $newarr=[
            ['value'=>0, 'name'=>'No'],
            ['value'=>1, 'name'=>'Yes A级'],
            ['value'=>2, 'name'=>'Yes B级'],
            ['value'=>3, 'name'=>'Yes 旧主记忆库'],
            ['value'=>4, 'name'=>'Yes 特殊项目公司主记忆库'],
            ['value'=>5, 'name'=>'N/A 不更库项目'],
            ['value'=>6, 'name'=>'N/A 已更特殊类型库'],

        ];

        $typearr = [
            ['value'=>0, 'name'=>'No'],
            ['value'=>1, 'name'=>'No 文件未达更类型库标准'],
            ['value'=>2, 'name'=>'Yes MD/IVD综述资料'],
            ['value'=>3, 'name'=>'Yes MD/IVD证书声明'],
            ['value'=>4, 'name'=>'Yes MD/IVD上市后资料'],
            ['value'=>5, 'name'=>'Yes MD/IVD风险管理'],
            ['value'=>6, 'name'=>'Yes MD/IVD安全有效基本要求清单'],
            ['value'=>7, 'name'=>'Yes MD/IVD产品技术要求'],
            ['value'=>8, 'name'=>'Yes MD/IVD产品注册检验报告'],
            ['value'=>9, 'name'=>'Yes MD/IVD物理测试'],
            ['value'=>10, 'name'=>'Yes MD/IVD化学测试'],
            ['value'=>11, 'name'=>'Yes MD/IVD IEC电气测试'],
            ['value'=>12, 'name'=>'Yes MD/IVD辐射测试'],
            ['value'=>13, 'name'=>'Yes MD/IVD软件'],
            ['value'=>14, 'name'=>'Yes MD/IVD生物学评价'],
            ['value'=>15, 'name'=>'Yes MD/IVD灭菌测试'],
            ['value'=>16, 'name'=>'Yes MD/IVD非临床研究资料'],
            ['value'=>17, 'name'=>'Yes MD/IVD临床研究资料'],
            ['value'=>18, 'name'=>'Yes MD/IVD说明书、标签'],
            ['value'=>19, 'name'=>'Yes MD/IVD质量管理'],
            ['value'=>20, 'name'=>'Yes MD/IVD单据'],
            ['value'=>21, 'name'=>'Yes CTD-质量'],
            ['value'=>22, 'name'=>'Yes 药理学'],
            ['value'=>23, 'name'=>'Yes 药代动力学'],
            ['value'=>24, 'name'=>'Yes 毒理学'],
            ['value'=>25, 'name'=>'Yes CTD-非临床'],
            ['value'=>26, 'name'=>'Yes CTD-临床'],
            ['value'=>27, 'name'=>'Yes 上市后报告'],
            ['value'=>28, 'name'=>'Yes PBRER、PSUR、DSUR'],
            ['value'=>29, 'name'=>'Yes RMP'],
            ['value'=>30, 'name'=>'Yes SUSAR'],
            ['value'=>31, 'name'=>'Yes 检验报告COA'],
            ['value'=>32, 'name'=>'Yes 药品符合性声明'],
            ['value'=>33, 'name'=>'Yes SMF'],
            ['value'=>34, 'name'=>'Yes DMF'],
            ['value'=>35, 'name'=>'Yes 药品说明书'],
            ['value'=>36, 'name'=>'Yes 肿瘤基因检测报告（NGS）'],
            ['value'=>37, 'name'=>'Yes 境外生产药品注册-补充申请表'],
            ['value'=>38, 'name'=>'Yes 财报'],
            ['value'=>39, 'name'=>'Yes 研究者手册'],
            ['value'=>40, 'name'=>'Yes 注册标准'],
            ['value'=>41, 'name'=>'Yes EIR'],
            ['value'=>42, 'name'=>'Yes 证书声明'],
            ['value'=>43, 'name'=>'Yes 软件'],
            ['value'=>44, 'name'=>'Yes 标准A级'],
            ['value'=>45, 'name'=>'Yes 标准B级'],
            ['value'=>46, 'name'=>'Yes 法规A级'],
            ['value'=>47, 'name'=>'Yes 法规B级'],
            ['value'=>48, 'name'=>'Yes 合同协议/法律文件A级'],
            ['value'=>49, 'name'=>'Yes 合同协议/法律文件B级'],
            ['value'=>50, 'name'=>'Yes 专利A级'],
            ['value'=>51, 'name'=>'Yes 专利B级'],
            ['value'=>52, 'name'=>'Yes 医学文献/论文A级'],
            ['value'=>53, 'name'=>'Yes 医学文献/论文B级'],
            ['value'=>54, 'name'=>'Yes 化学品分析A级'],
            ['value'=>55, 'name'=>'Yes 化学品分析B级'],
            ['value'=>56, 'name'=>'Yes 发补/补正通知'],

            ['value'=>58, 'name'=>'Yes 小语种A级'],
            ['value'=>59, 'name'=>'Yes 小语种B级'],
            ['value'=>60, 'name'=>'Yes 个人A级'],
            ['value'=>61, 'name'=>'Yes 个人B级'],

        ];

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
		
		// 更新方式
		$up = Db::name('xt_dict')->where('c_id',11)->select();


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

        return view('form-project_database-view', [
            'info'=>$res,'File_Type'=>$File_Type,'document_type'=>json_encode($document_type), 'yy'=>$yy, 'up'=>$up,
            'tr'=>json_encode($tr), 're'=>json_encode($re), 'yp'=>json_encode($yp), 'hp'=>json_encode($hp), 'pa'=>$pa,'newarr'=>$newarr,'typearr'=>$typearr,'ncr'=>json_encode($ncr),
        ]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 更新时间
        $data['Update_Time'] = date("Ymd");
        $data['Date'] = substr($data['Filing_Code'], 2, 8);
        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        PjProjectDatabaseModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request,$id)
    {
        // 获取提交的数据
        $data = $request->put();

        $value = Db::name('pj_project_database')->where('id',$id)->value('Update_Company_TM');
        if($value != $data['Update_Company_TM']){
            // 更新时间
            $data['Update_Time'] = date("Ymd");
        }


        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        PjProjectDatabaseModel::update($data,['id' => $id]);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        PjProjectDatabaseModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
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
            $res = Db::name('pj_project_database')->wherein('id',$data['arr'])->update($arr);

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