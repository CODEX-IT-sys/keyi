<?php

namespace app\common\model;

use think\Db;
use think\Exception;
use think\Model;
use think\Paginator;
use think\model\concern\SoftDelete;

// 客户要求 模型
class MkRequirement extends Model
{
    // 软删除
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    /**
     * 获取列表
     * @param string $search_type 查询类型
     * @param string|array $field 搜索字段
     * @param string|array $keyword 搜索关键词
     * @param int $limit 每页显示数据条数
     * @return Paginator
     * @throws \think\exception\DbException
     */
    public function getList($search_type, $field = '', $keyword = '', $limit = 50,$simple = 1)
    {
        $name = session('administrator')['name'];

        $where = [];

        $query = $this;
        $a = '最新';
        if($simple == 3){
            $query = $query->where(function ($query) use ($a) {
                $query->where('Formatting','like','%【最新】%')->whereor('Translation_Scope','like','%【最新】%')
                    ->whereor('Language_Style','like','%【最新】%')->whereor('Terminology','like','%【最新】%')
                    ->whereor('Deliverables','like','%【最新】%');
            });
        }
        if($simple == 4){
            $query = $query->where(function ($query) use ($a) {
                $query->where('Formatting','like','%【作废】%')->whereor('Translation_Scope','like','%【作废】%')
                    ->whereor('Language_Style','like','%【作废】%')->whereor('Terminology','like','%【作废】%')
                    ->whereor('Deliverables','like','%【作废】%');
            });
        }


        // 查询器对象 判断管理层
        if (!in_array(session('administrator')['job_id'], [1, 6, 8, 9, 20]) ) {
            // 否则 就只显示自己录入的数据
            $where1 = [
                'PTL' => $name
            ];
            $where2 = [
                'SA' => $name
            ];
            $query = $query->where(function ($query) use ($where1,$where2) {
                $query->where($where1)->whereor($where2);
            });
        }


        // 如果有搜索类型，添加查询条件
        $field_arr = explode(',', $field);//字段数组
        $keyword_arr = explode(',', $keyword);//关键词数组

        // 字段不为空
        if ($search_type == 'and') {

            //多字段 且 查询
            $query = $query->where(function ($query) use ($field_arr, $keyword_arr) {
                foreach ($field_arr as $k => $v) {
                    foreach ($keyword_arr as $key => $val) {
                        if ($k == $key) {
                            $query->where($v, 'like', "%$val%");
                        }
                    }
                }
            });

        } else {
            //多字段 或 查询
            $query = $query->where(function ($query) use ($field_arr, $keyword_arr) {
                foreach ($field_arr as $k => $v) {
                    foreach ($keyword_arr as $key => $val) {
                        if ($k == $key) {
                            $query->whereXor($v, 'like', "%$val%");
                        }
                    }
                }
            });
        }

        // 返回分页对象
        return $query->where($where)->order('Date desc, id desc')->paginate($limit);
    }

    public function getList2($search_type, $field = '', $keyword = '', $limit = 50,$simple = 1)
    {
        $name = session('administrator')['name'];

        $where = [];

        $query = $this;
        $a = '最新';
        if($simple == 3){
            $query = $query->where(function ($query) use ($a) {
                $query->where('Formatting','like','%【最新】%')->whereor('Translation_Scope','like','%【最新】%')
                    ->whereor('Language_Style','like','%【最新】%')->whereor('Terminology','like','%【最新】%')
                    ->whereor('Deliverables','like','%【最新】%');
            });
        }
        if($simple == 4){
            $query = $query->where(function ($query) use ($a) {
                $query->where('Formatting','like','%【作废】%')->whereor('Translation_Scope','like','%【作废】%')
                    ->whereor('Language_Style','like','%【作废】%')->whereor('Terminology','like','%【作废】%')
                    ->whereor('Deliverables','like','%【作废】%');
            });
        }

        //判断审批通过的
        $where_app = [
            'belong' => '客户要求',
            'name' => $name,
            'status' => 1
        ];
        $approval = Db::name('pj_approval')->where($where_app)->select();
        $arr_id = [];
        foreach($approval as $key=>$val){
            $l = Db::name('mk_requirement')
                ->where('Customer_Company',$val['company'])
                ->where('Attention',$val['attention'])
                ->where('delete_time',0)
                ->field('id')
                ->select();
            $id = array_column($l,'id');
            $arr_id = array_merge($arr_id,$id);
        }

        // 查询器对象 判断管理层
        if (!in_array(session('administrator')['job_id'], [1, 6, 8, 9, 20]) && !in_array($name,['曹丽丽','张攀','闻心宇'])) {
            // 否则 就只显示自己录入的数据
            $where1 = [
                'PTL' => $name
            ];
            //$where2['id'] = array('in','301,172');
            $query = $query->where(function ($query) use ($where1,$arr_id) {
                $query->where($where1)->whereor('id','in',$arr_id);
            });
        }





        // 如果有搜索类型，添加查询条件
        $field_arr = explode(',', $field);//字段数组
        $keyword_arr = explode(',', $keyword);//关键词数组

        // 字段不为空
        if ($search_type == 'and') {

            //多字段 且 查询
            $query = $query->where(function ($query) use ($field_arr, $keyword_arr) {
                foreach ($field_arr as $k => $v) {
                    foreach ($keyword_arr as $key => $val) {
                        if ($k == $key) {
                            $query->where($v, 'like', "%$val%");
                        }
                    }
                }
            });

        } else {
            //多字段 或 查询
            $query = $query->where(function ($query) use ($field_arr, $keyword_arr) {
                foreach ($field_arr as $k => $v) {
                    foreach ($keyword_arr as $key => $val) {
                        if ($k == $key) {
                            $query->whereXor($v, 'like', "%$val%");
                        }
                    }
                }
            });
        }

        // 返回分页对象
        return $query->where($where)->order('Date desc, id desc')->paginate($limit);
    }

    public function getAll()
    {
        return $this->order('id asc')->select();
    }

    /**
     * 获取信息
     * @param int $id 主键ID
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
     * @param int $id
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