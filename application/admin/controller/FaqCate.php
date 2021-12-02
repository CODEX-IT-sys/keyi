<?php
namespace app\admin\controller;


//FAQ分类管理
use app\common\model\Admin;
use app\common\controller\Common;
use app\facade\FaqCate as FaqCateModel;
use think\Db;
use think\Request;
use think\Controller;
class FaqCate extends Common
{
    // 验证失败抛出异常
    protected $failException = true;

    public function index(Request $request, $search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        // 数据库表字段集
        $colsData = getAllField('ky_faq_cate');

        foreach ($colsData as $k=>$v)
        {
            switch($v['Field']){
                case 'id':

                    $colsData[$k]['title']='ID';
                    break;
                case 'update_time':

                    $colsData[$k]['fixed']='left';
                    $colsData[$k]['hide']='true';
                    break;
                case 'create_time':
                    $colsData[$k]['hide']='true';
                    break;
                default:


            }

        }

        // 查询文本说明信息
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

        // 调用模型获取列表
        $list = FaqCateModel::getList($search_type, $field, $keyword, $limit);

        // 返回数据
        return json(generate_layui_table_data($list));
    }



    // 搜索弹框
    public function condition()
    {
        // 数据库表字段集
        $colsData = getAllField('ky_faq_cate');

        // 直接返回视图
        return view('', ['select_field'=>$colsData]);
    }

    // 显示新建的表单页
    public function create()
    {
        // 文件分类

        // 直接返回视图
        return view('form-faq');
    }

    // 查看
    public function read($id)
    {

    }

    //编辑视图
    public function edit($id)
    {
        // 查询信息
        $res = FaqCateModel::get($id);


        return view('form-faq-view', [
            'info'=>$res,
        ]);
    }

    // 新建/更新 保存数据
    public function save(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();


        // 保存
        FaqCateModel::create($data);

        // 返回操作结果
        $this->redirect('index');
    }

    // 更新
    public function update(Request $request)
    {
        // 获取提交的数据
        $data = $request->post();


        Db::name('faq_cate')->where('id',$data['id'])->update($data);

        echo "<script>history.go(-2);</script>";

        // 返回操作结果
        //$this->redirect('index');
    }

    // 删除
    public function delete($id)
    {
        // 调用模型删除
        FaqCateModel::destroy($id);

        // 返回数据
        return json(['msg' => '删除成功']);
    }



    // 异步获取 关联信息
    public function get_info($code)
    {

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