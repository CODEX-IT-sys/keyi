<?php
namespace app\common\model;

use think\Db;
use think\Exception;
use think\Model;
use think\Paginator;
use think\model\concern\SoftDelete;

// 项目汇总 模型
class PjContractReview extends Model
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
    public function getList($search_type = '', $field = '', $keyword = '', $limit = 50)
    {
        $name = session('administrator')['name'];

        $job_id = session('administrator')['job_id'];

        $query = $this;

        // 查询器对象 判断管理层
        if(in_array($job_id, [1,8,9,20])) {

            $query = $this;

            // 翻、校、排、助理
        }else if(in_array($job_id, [7,10,11,12,13,19])) {

            $field_name = '';

            if($job_id == 7){
                $field_name = 'PA';
            } else if($job_id == 10 or $job_id == 19){
                $field_name = 'Translator';
            } else if($job_id == 11){
                $field_name = 'Reviser';
            } else if($job_id == 12){
                $field_name = 'Pre_Formatter';
            } else if($job_id == 13){
                $field_name = 'Post_Formatter';
            }

            // 否则 就只显示自己录入和参与的数据
            $query = $this->where(function ($query) use($field_name, $name) {
                $query->where($field_name, 'like', "%$name%");
            });

            // codex 校长、副校长、质控主管
        }else if(in_array($job_id, [4,6,15])) {

            $query = $this->where(function ($query) use($name) {
                $query->where('Translator', 'like', "%$name%")
                    ->whereXor('Reviser', 'like', "%$name%");
            });

        }else if($job_id = 5) { // 培训专员

            $query = $this->where(function ($query) use($name) {
                $query->where('Pre_Formatter', 'like', "%$name%")
                    ->whereXor('Post_Formatter', 'like', "%$name%");
            });

        }else{
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
        return $query->order('Date desc, id desc')->paginate($limit)->each(function($item, $key){

        $expect = strtotime($item["Delivery_Date_Expected"]); //客户期望日期
            $expect = strtotime(date('Ymd',$expect));

        $completed = strtotime($item["Completed"]);
        $item['Early_days'] = round(($completed - $expect)/86400);
        if($item['Early_days'] >100 || $item['Early_days'] < -100){
            $item['Early_days'] = -999;
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