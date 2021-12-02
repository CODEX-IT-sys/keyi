<?php


namespace app\common\model;

use think\Db;
use think\Exception;
use think\Model;
use think\Paginator;
use think\model\concern\SoftDelete;

class FaqCate extends Model
{
    // 软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public function getList($search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        $name = session('administrator')['name'];

        $job_id = session('administrator')['job_id'];

        $query = $this;

        // 查询器对象 判断管理层


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
        return $query->order('id desc')->paginate($limit);
    }

    public function getAll()
    {
        return $this->order('id asc')->select();
    }

    /**
     * 获取信息
     * @param int   $id    主键ID
     * @param array $field 要查询的字段
     * @return Model
     * @throws \think\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne($id, $field)
    {
        return $this->field($field)->where('id', $id)->findOrFail();
    }

    /**
     * 根据ID，获取字段值
     * @param int    $id
     * @param string $field 要查询的字段名
     * @return int|string
     * @throws ModelNotFoundException
     */
    public function getFieldById($id, $field)
    {
        $value = $this->where('id', $id)->value($field);

        if (!$value) {
            throw new ModelNotFoundException("找不到指定的{$field}字段值");
        }

        return $value;
    }
}