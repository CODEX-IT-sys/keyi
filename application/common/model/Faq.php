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

        // 查询器对象 判断管理层
        $query = $this->alias('a')
            ->join('faq_cate c', 'a.cate_id = c.id')
            ->field('a.*, c.cn_name')
            ->where('c.cn_name', '=', $cate);

        if($field == 'cate_id'){

            $query = $this->alias('a')
                ->join('faq_cate c', 'a.cate_id = c.id')
                ->field('a.*, c.cn_name')
                ->where('c.cn_name', 'like', "%$keyword%");

        }else{

            $query = $query->where($field, 'like', "%$keyword%");
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