<?php
namespace app\admin\controller;


//FAQ管理
use app\common\model\Admin;
use app\common\controller\Common;
use app\facade\Faq as FaqModel;
use think\Db;
use think\Request;
use think\Controller;
class Faq extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_faq');

        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'title':
                    $colsData[$k]['width']=300;
                    $colsData[$k]['fixed']='left';
                    $colsData[$k]['sort']='true';
                    break;
                case 'content':
                    $colsData[$k]['hide']='true';
                    break;
                case 'update_time':
                    $colsData[$k]['hide']='true';
                    break;
                case 'create_time':
                    $colsData[$k]['hide']='true';
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide']='true';
                    break;
                default:;

            }

        }

        // 查询分类信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');

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

        $cate = '电脑软件';

        // 调用模型获取列表
        $list = FaqModel::getList($search_type, $field, $keyword, $limit,$cate);


        // 返回数据
        return json(generate_layui_table_data($list));
    }

    public function fanyi(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_faq');

        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'title':
                    $colsData[$k]['width']=300;
                    $colsData[$k]['fixed']='left';
                    $colsData[$k]['sort']='true';
                    break;
                case 'content':
                    $colsData[$k]['hide']='true';
                    break;
                case 'update_time':
                    $colsData[$k]['hide']='true';
                    break;
                case 'create_time':
                    $colsData[$k]['hide']='true';
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide']='true';
                    break;
                default:;

            }

        }

        // 查询分类信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');

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
        $cate = '翻译';
        // 调用模型获取列表
        $list = FaqModel::getList($search_type, $field, $keyword, $limit,$cate);


        // 返回数据
        return json(generate_layui_table_data($list));
    }
    public function paiban(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_faq');

        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'title':
                    $colsData[$k]['width']=300;
                    $colsData[$k]['fixed']='left';
                    $colsData[$k]['sort']='true';
                    break;
                case 'content':
                    $colsData[$k]['hide']='true';
                    break;
                case 'update_time':
                    $colsData[$k]['hide']='true';
                    break;
                case 'create_time':
                    $colsData[$k]['hide']='true';
                    break;
                case 'Filled_by':
                    $colsData[$k]['hide']='true';
                    break;
                default:;

            }

        }

        // 查询分类信息
        $intro = Db::name('xt_table_text')->where('id',6)->value('intro');

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
        $cate = '排版';
        // 调用模型获取列表
        $list = FaqModel::getList($search_type, $field, $keyword, $limit,$cate);


        // 返回数据
        return json(generate_layui_table_data($list));
    }
    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_faq');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 文件分类
        $cate = Db::name('faq_cate')->where('delete_time',0)->select();

        // 直接返回视图
        return view('form-faq',[
            'cate' => $cate,
        ]);
    }

    // 查看
    public function read($id)
    {
        // 查询信息
        $res = FaqModel::get($id);
        // 直接返回视图
        return view('read',[
            'info'=>$res,
        ]);
    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = FaqModel::get($id);

        $cate_id = $res['cate_id'];

        // 文件分类
        $cate = Db::name('faq_cate')->select();

        return view('form-faq-view', [
            'info'=>$res,'cate'=>$cate,
        ]);
    }

    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();

        // 写入填表人
        $data['Filled_by'] = session('administrator')['name'];

        // 保存
        FaqModel::create($data);

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

        $upData = [
          'title' =>$data['title'],
            'dec' => $data['dec'],
            'cate_id' => $data['cate_id'],
            'content' =>$data['content'],
            'Filled_by' =>$data['Filled_by'],
            'update_time' => date('Ymd'),
        ];

        Db::name('faq')->where('id',$data['id'])->update($upData);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 删除
    public function delete($id)
    {

        // 调用模型删除
        FaqModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
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

    public function upload(Request $request)
    {
        //设置php 不超时
        @ini_set('memory_limit', '-1');
        @set_time_limit(0);
        $param = $request->param();
        $file = $request->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(  'uploads');
            if ($info) {
                $url ='/uploads/'. $info->getSaveName();
                $data['code'] = 0;
                $data['msg'] = '';
                $data['data']['src'] = $url ;
                return json($data);
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }

}