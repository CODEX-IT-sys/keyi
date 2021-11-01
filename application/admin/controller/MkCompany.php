<?php
namespace app\admin\controller;

use app\common\controller\Common;
use app\facade\MkCompany as MkCompanyModel;
use think\Controller;
use think\Request;
use think\Db;

class MkCompany extends Common
{
    // 显示列表
    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_company');

        $name = session('administrator')['name'];

        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'Update_Date':
                    $colsData[$k]['hide']=true;
                    break;
                case 'Company_Full_Name':
                    $colsData[$k]['width']=300;
                    break;
                case 'Telephone_Number':
                    $colsData[$k]['width']=200;
                    break;
                case 'Mobile':
                    $colsData[$k]['width']=200;
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide']=true;
                    break;
                default:
                    $colsData[$k]['width']=100;

            }
        }

        // 查询文本说明信息
        $intro = Db::name('xt_table_text')->where('id',2)->value('intro');
        $tag = 0;
        //判断是否是查询全局公司名称
        if(!is_array($field)){
            if($field == 'Company_Name'){
                $res = Db::name('mk_company')->where('Company_name',$keyword)->where('delete_time',0)->find();
                if($res['Filled_by'] != $name){
                    foreach ($colsData as $k=>$v)
                    {
                        switch($v['Field']){
                            case 'Register_Date':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Update_Date':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Sales':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Company_Full_Name':
                                $colsData[$k]['width']=300;
                                break;
                            case 'Attention':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Department':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Company_Address':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Country':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Telephone_Number':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Mobile':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Email':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Remarks':
                                $colsData[$k]['hide']=true;
                                break;
                            case 'Filled_By':
                                $colsData[$k]['hide']=true;
                                break;

                            default:
                                $colsData[$k]['width']=100;

                        }
                    }

                    $tag = 1;
                }

            }
        }


        // 非Ajax请求，直接返回视图
        if (!$request->isAjax()) {

            return view('', [
                'select_field'=>$colsData, 'colsData' => json_encode($colsData),
                'intro'=>$intro, 'field'=>$field, 'keyword'=>$keyword,'tag'=>$tag,
            ]);
        }

        // 调用模型获取列表
        $list = MkCompanyModel::getList($search_type, $field, $keyword, $limit);

        if(!is_array($field)){
            if($field == 'Company_Name'){
                $list = Db::name('mk_company')->where('Company_name',$keyword)->where('delete_time',0)->select();

                return [
                    'code'  => 0,
                    'msg'   => '',
                    'count' => 0,
                    'data'  =>$list,
                ];
            }


        }



        // 返回数据
        return json(generate_layui_table_data($list));
    }

    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_mk_company');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 直接返回视图
        return view('form-Company');
    }


    // 查看
    public function read($id)
    {

        // 查询信息
        $res = MkCompanyModel::get($id);

        // 主体公司
        $gs = Db::name('xt_company')->field('id,cn_name,en_name')->select();

        // 主体公司ID
        $gs_id = Db::name('xt_company')->where('cn_name',$res['Subject_Company'])
            ->whereOr('en_name',$res['Subject_Company'])->value('id');

        // 直接返回视图
        return view('form-Customer-view',['info'=>$res, 'gs'=>$gs, 'gs_id'=>$gs_id]);
    }

    //编辑视图
    public function edit($id)
    {

        // 查询信息
        $res = MkCompanyModel::get($id);

        return view('form-Company-view',
            ['info'=>$res]);
    }

    // 新建 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();
        $res = Db::name('mk_company')
            ->where('Company_Name',$data['Company_Name'])
            ->where('delete_time',0)
            ->find();
        if($res){
            $this->error('该公司已记录，请勿重复添加');
        }
        // 登记日期
        $data['Register_Date'] = date("Ymd");

        // 更新时间
        $data['Update_Date'] = date("Ymd");

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];


        // 保存
        MkCompanyModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->put();

        // 更新时间
        $data['Update_Date'] = date("Ymd");

        MkCompanyModel::update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 单条删除
    public function delete($id)
    {
        // 调用模型删除
        MkCompanyModel::destroy($id);

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
            MkCompanyModel::destroy($v);
        }

        // 返回数据
        return json(['msg' => '删除成功']);
    }

    //输入公司名称异步获取信息
    public function get_info($company_name){
        $info = Db::name('mk_contract')
            ->where('Company_Name',$company_name)
            ->where('delete_time',0)
            ->find();
        if($info){
            $info['Status'] = '已合作';
        }
        return json([
            'data'=>$info
        ]);
    }

    //公司名称效验
    public function inspection(Request $request)
    {
        try {
            $data=$request->param();
            $res = Db::name('mk_company')->wherein('Company_Name',$data['Company_Name'])->where('delete_time',0)->count();
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
}