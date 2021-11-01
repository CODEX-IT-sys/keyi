<?php
namespace app\common\model;

use think\Db;
use think\Exception;
use think\Model;
use think\Paginator;
use think\model\concern\SoftDelete;

class MkCompany extends Model
{
    // 软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    /**
     * 获取列表
     * @param   string            $search_type    查询类型
     * @param   string|array      $field          搜索字段
     * @param   string|array      $keyword        搜索关键词
     * @param   int               $limit          每页显示数据条数
     * @return Paginator
     * @throws \think\exception\DbException
     */
    public function getList($search_type , $field = '', $keyword = '', $limit = 50)
    {
        $name = session('administrator')['name'];

        $where = [];

        $query = $this;

        // 查询器对象 判断管理层
        if(!in_array(session('administrator')['job_id'],[1,8,9,20])) {
            // 否则 就只显示自己录入的数据
            $where['Filled_by'] = $name;
        }

        // 如果有搜索类型，添加查询条件
        $field_arr = explode(',' , $field);//字段数组
        $keyword_arr = explode(',' , $keyword);//关键词数组



        // 字段不为空
        if($search_type == 'and'){

            //多字段 且 查询
            $query = $query->where(function ($query) use($field_arr, $keyword_arr) {
                foreach ($field_arr as $k => $v){
                    foreach ($keyword_arr as $key => $val){
                        if($k == $key){
                            $query->where($v, 'like', "%$val%");
                        }
                    }
                }
            });

        }else{
            //多字段 或 查询
            $query = $query->where(function ($query) use($field_arr, $keyword_arr) {
                foreach ($field_arr as $k => $v){
                    foreach ($keyword_arr as $key => $val){
                        if($k == $key) {
                            $query->whereXor($v, 'like', "%$val%");
                        }
                    }
                }
            });
        }

        // 返回分页对象
        return $query->where($where)->order('Register_Date desc, id desc')->paginate($limit);
    }

}