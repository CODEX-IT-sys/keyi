<?php


namespace app\common\model;

use think\Db;
use think\Exception;
use think\Model;
use think\Paginator;
use think\model\concern\SoftDelete;

class Faq extends Model
{
    // 软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    // 所属分类 获取器
    public function getCateIdAttr($value)
    {
        $bm = db('faq_cate')->field('id, cn_name')->select();

        foreach ($bm as $k => $v)
        {
            $bm[$v['id']] = $v['cn_name'];
        }

        $cateId = $bm;

        return $cateId[$value];
    }

    public function getList($search_type = '', $field = '', $keyword = '', $limit = 50,$cate = '电脑软件')
    {
        $name = session('administrator')['name'];

        $job_id = session('administrator')['job_id'];

        $query = $this;
        //获取分类
        $query = $this->alias('a')
            ->join('faq_cate c', 'a.cate_id = c.id')
            ->field('a.*, c.cn_name')
            ->where('c.cn_name', '=', $cate);

        // 如果有搜索类型，添加查询条件
        if ($search_type != '') {

            $field_arr = explode(',' , $field);//字段数组
            $keyword_arr = explode(',' , $keyword);//关键词数组

            // 字段不为空
            if($search_type == 'and'){
                //多字段 且 查询
                $query = $query->where(function ($query) use($field_arr, $keyword_arr) {
                    foreach ($field_arr as $k => $v){
                        foreach ($keyword_arr as $key => $val){
                            if($k == $key) {
                                if ($v == 'cate_id') {
                                    $query = $this->alias('a')
                                        ->join('faq_cate c', 'a.cate_id = c.id')
                                        ->field('a.*, c.cn_name')
                                        ->where('c.cn_name', 'like', "%$val%");

                                } else {
                                    $query = $query->where($v, 'like', "%$val%");
                                }
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
                                if($v == 'cate_id'){
                                    $query = $this->alias('a')
                                        ->join('faq_cate c', 'a.cate_id = c.id')
                                        ->field('a.*, c.cn_name')
                                        ->whereXor('c.cn_name', 'like', "%$val%");

                                }else{
                                    $query = $query->whereXor($v, 'like', "%$val%");
                                }

                            }
                        }
                    }
                });



            }
        }else{
            // 字段不为空
            if(!empty($field)){// 单字段查询

                if($field == 'cate_id'){

                    $query = $this->alias('a')
                        ->join('faq_cate c', 'a.cate_id = c.id')
                        ->field('a.*, c.cn_name')
                        ->where('c.cn_name', 'like', "%$keyword%");

                }else{

                    $query = $query->where($field, 'like', "%$keyword%");
                }
            }
        }



        return $query->order('id desc')->paginate($limit)->each(function($item, $key){
            $name = session('administrator')['name'];
            $status = $item['status'];
            if(!empty($status)){
                $status_arr = explode(',',$status);
            }else{
                $status_arr = [];
            }

            if(in_array($name,$status_arr)){
                $item['ev_status'] = 1;
            }else{
                $item['ev_status'] = 0;
            }

            return $item;

        });
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