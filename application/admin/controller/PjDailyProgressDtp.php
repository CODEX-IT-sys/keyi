<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\PjContractReview as PjContractReviewModel;
use app\facade\PjProjectProfile as PjProjectProfileModel;
use app\facade\PjDailyProgressDtp as PjDailyProgressDtpModel;
use think\Controller;
use think\Request;
use think\Db;

// 每日进度（排版） 控制器
class PjDailyProgressDtp extends Common
{

    // 验证失败抛出异常
    protected $failException = true;

    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_daily_progress_dtp');
        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){

                case 'Name_of_Formatter':
                    $colsData[$k]['fixed']='left';
                    break;
                case 'Work_Date':
                    $colsData[$k]['fixed']='left';
                    break;
                case 'Job_Name':
                    $colsData[$k]['fixed']='left';
                    break;
                default:
                    $colsData[$k]['width']=80;
            }

        }
        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',10)->value('intro');
        if($request->has('search_type')){
            $data= $request->only(['search_type']);
            $search_type=$data ["search_type"];
        }
        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {
            return view('', [
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),
                'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword,
                'search_type'=>$search_type
            ]);
        }

        // 调用模型获取列表
        $list = PjDailyProgressDtpModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_pj_daily_progress_dtp');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 查询 可供预选的 文件编码
        $file_code = PjContractReviewModel::field('id, Filing_Code')
            ->order('id desc')->limit(30000)->select();
			
		// 文件类型
		$File_Type = Db::name('xt_dict')->where('c_id',4)->select();
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 排版难度
		$pb = Db::name('xt_dict')->where('c_id',7)->select();

        // 工作内容 下拉选项
        $Work_Content = Db::name('xt_dict')->where('c_id',13)->select();

        // 直接返回视图
        return view('form-daily_progress_dtp', [
            'file_code'=>$file_code, 'File_Type'=>$File_Type,
            'yy'=>$yy, 'pb'=>$pb, 'Work_Content'=>$Work_Content
        ]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = PjDailyProgressDtpModel::get($id);
		
		// 文件类型
		$File_Type = Db::name('xt_dict')->where('c_id',4)->select();
		
		// 语种
		$yy = Db::name('xt_dict')->where('c_id',1)->select();
		
		// 排版难度
		$pb = Db::name('xt_dict')->where('c_id',7)->select();
		
		// 工作内容 下拉选项
		$Work_Content = Db::name('xt_dict')->where('c_id',13)->select();

        return view('form-daily_progress_dtp-view', [
            'info'=>$res, 'File_Type'=>$File_Type, 'yy'=>$yy,
            'pb'=>$pb, 'Work_Content'=>$Work_Content
        ]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();
        //获取工作内容页数和
        $ysh   =Db::name('pj_daily_progress_dtp')->where('Filing_Code',$data['Filing_Code'])->where('Job_Name',$data['Job_Name'])->where('Name_of_Formatter',$data['Name_of_Formatter'])
            ->where('Work_Content',$data['Work_Content'])->sum('Number_of_Pages_Completed');

        //计算项目描述表中的文件编号的页数
        $xmms=Db::name('pj_project_profile')->where('Filing_Code',$data['Filing_Code'])->where('Job_Name',$data['Job_Name'])->value('Pages');
        if($ysh+$data['Number_of_Pages_Completed']>$xmms){
            return $this->error('该文件完成页数和和超过项目描述页数');
        }

        //判断是否自动生成预排评估
        if($data['Work_Content']=='Postformat' && $data['Percentage_Completed'] == 100){

            //查询项目描述表中的y排版人员
            $xmyp = Db::name('pj_project_profile')->where('Filing_Code', $data['Filing_Code'])->where('Job_Name', $data['Job_Name'])->value('Pre_Formatter');
            //如果没有预排人员 则不生成记录
            if($xmyp != '' && $xmyp != 'N/A'){
                //获取y排版人员个数
                $record=Db::name('pj_daily_progress_dtp')->where('Filing_Code',$data['Filing_Code'])->where('Job_Name',$data['Job_Name'])
                    ->where('Work_Content','Preformat')->where('Percentage_Completed',100)->count();

                if($record == 1){
                    $ypb = Db::name('pj_daily_progress_dtp')->where('Filing_Code',$data['Filing_Code'])->where('Job_Name',$data['Job_Name'])
                        ->where('Work_Content','Preformat')->where('Percentage_Completed',100)->find();

                    if($ypb){
                        if($ypb['Name_of_Formatter'] != $data['Name_of_Formatter']){
                            $name = session('administrator')['name'];

                            $pg_data = [
                                'Filing_Code' => $data['Filing_Code'],
                                'Job_Name' => $data['Job_Name'],
                                'Pre_Formatter' => $ypb['Name_of_Formatter'],
                                'Company_Name' => $ypb['Company_Name'],
                                'Language' => $ypb['Language'],
                                'Format_Difficulty' => $ypb['Format_Difficulty'],
                                'Filled_by' => $name,
                                'create_time' => time(),
                            ];
                            $where = [
                                'Filing_Code' => $data['Filing_Code'],
                                'Job_Name' => $data['Job_Name'],

                                'delete_time' => 0,
                            ];
                            //如果评估记录不存在则添加
                            $res =  Db('pj_y_p_evaluation')->where($where)->find();
                            //生成预排评估记录
                            if(!$res){
                                Db('pj_y_p_evaluation')->insert($pg_data);
                            }

                        }
                    }
                }
            }


        }
        // 计算实际用时
        $s = strtotime($data['Start_Time']);
        $e = strtotime($data['End_Time']);

        $z = strtotime($data['Work_Date']. ' 12:00');

        $timediff = $e - $s;
        $remain = $timediff%86400;
        $hours = round($remain/3600, 2); // 保留2位小数

        // 写入实际工作小时(中午休息一小时)
        if($s < $z && $e > $z){
            $data['Actual_Work_Time'] = $hours - 1;
        }else{
            $data['Actual_Work_Time'] = $hours;
        }

        // 计算效率  完成页码/实际工作时间    保留2位小数
        if($hours != '' or 0){

            $data['Productivity'] = round($data['Number_of_Pages_Completed']/$data['Actual_Work_Time'],2);

        }else{
            $data['Productivity'] = 0;
        }

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        PjDailyProgressDtpModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        //获取工作内容页数和
        $ysh   =Db::name('pj_daily_progress_dtp')->where('Filing_Code',$data['Filing_Code'])->where('Job_Name',$data['Job_Name'])->where('Name_of_Formatter',$data['Name_of_Formatter'])
            ->where('Work_Content',$data['Work_Content'])->where('delete_time',0)->sum('Number_of_Pages_Completed');

        //计算项目描述表中的文件编号的页数
        $xmms=Db::name('pj_project_profile')->where('Filing_Code',$data['Filing_Code'])->where('Job_Name',$data['Job_Name'])->value('Pages');

        //去除正在修改的页数
        $page=Db::name('pj_daily_progress_dtp')->where('id',$data['id'])->value('Number_of_Pages_Completed');

        if($ysh+$data['Number_of_Pages_Completed']-$page>$xmms){
            return $this->error('该文件完成页数和和超过项目描述页数');
        }

        /*if($data['Work_Content']=='Postformat'){
            //获取y排版人员
            $ypb=Db::name('pj_daily_progress_dtp')->where('Filing_Code',$data['Filing_Code'])->where('Job_Name',$data['Job_Name'])
                ->where('Work_Content','Preformat')->select();
            $man=[];
            foreach ($ypb as $k=>$v)
            {
                $man[]=$v['Name_of_Formatter'];
            }
            if(in_array($data['Name_of_Formatter'],$man)){
                $data['Number_of_Pages_Completed']=0;
            }
        }*/

        // 计算实际用时
        $s = strtotime($data['Start_Time']);
        $e = strtotime($data['End_Time']);

        // 中午休息 时间点
        $z = strtotime($data['Work_Date']. ' 12:00');

        $timediff = $e - $s;
        $remain = $timediff%86400;
        $hours = round($remain/3600, 2); // 保留2位小数

        // 写入实际工作小时(中午休息一小时)
        if($s < $z && $e > $z){
            $data['Actual_Work_Time'] = $hours - 1;
        }else{
            $data['Actual_Work_Time'] = $hours;
        }

        // 计算效率  完成页码/实际工作时间    保留2位小数
        if($hours != '' or 0){ // 分母不能为0

            $data['Productivity'] = round($data['Number_of_Pages_Completed']/$data['Actual_Work_Time'],2);

        }else{
            $data['Productivity'] = 0;
        }

        PjDailyProgressDtpModel::update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 删除(防止大量重复数据、使用真实删除)
    public function delete($id)
    {
        // 调用模型删除
        PjDailyProgressDtpModel::destroy($id, true);

        // 返回数据
        return json(['msg' => '删除成功']);
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
            ->field('Company_Name, File_Type, Language, Format_Difficulty')
            ->where('Job_Name', $name)->find();

        // 返回值
        return json(['data'=>$info]);
    }

    public function Batch_edit(Request $request)
    {

        try {
            $data=$request->param();
            $res = Db::name('pj_daily_progress_dtp')->wherein('id',$data['arr'])->update([$data['field']=>$data['numsss']]);
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