<?php
namespace app\common\model;

use think\Db;
use think\Exception;
use think\Model;
use think\Paginator;
use think\model\concern\SoftDelete;

// 项目描述 模型
class PjProjectProfile extends Model
{
    // 软删除
//    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;




    //反向关联项目描述
//    public function schedule()
//    {
//        return $this->hasOne('ZSchedule','profile_id','id');
//    }







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

        $where = [];
        $query = $this;
        if($name=="周美玲"){
            //
            $query = $this->where(function ($query) use($name) {
                $query->where('PA', ['eq', 'PA01'], ['eq', 'PA05'],['eq', 'PA06'], ['eq', 'PA12'],'or');
            });

        }elseif($name=="张攀" || $name=="闻心宇" || $name == "张甜"){
            $query = $this;
        }elseif($name=="李晓艺"){
            $query = $this->where(function ($query) use($name) {
                $query->where('PA', ['eq', 'PA04'], ['eq', 'PA05'],'or');
            });
        }else{

            // 查询器对象 判断管理层
            if(in_array($job_id, [1,6,8,9,16,20,22,23])) {

                $query = $this;

                // 翻、校、排、助理
            }else if(in_array($job_id, [7,10,11,12,13,19])) {

                if ($job_id == 7) {
                    $field_name = 'PA';
                    $query = $this->where(function ($query) use ($field_name, $name) {
                        $query->where($field_name, 'like', "%$name%");
                    });
                } else {

                    //判断是否是老师
                    $t = Db::name('pj_teacher_student')
                        ->where('delete_time',0)
                        ->where('pid',0)
                        ->where('name',$name)
                        ->find();

                    if($t) {
                        $stu = Db::name('pj_teacher_student')
                            ->where('delete_time', 0)
                            ->where('pid', $t['id'])
                            ->select();

                        $id_arr = [];
                        foreach($stu as $k3 => $v3){
                            $uname = $v3['name'];
                            $pn = Db::name('pj_project_profile')
                                ->where('Translator', 'like', "%$uname%")
                                ->whereOr('Reviser', 'like', "%$uname%")
                                ->whereOr('Pre_Formatter', 'like', "%$uname%")
                                ->whereOr('Post_Formatter', 'like', "%$uname%")
                                ->field('id')
                                ->select();
                            $arr2 = array_column($pn,'id');
                            $id_arr = array_merge($id_arr,$arr2);
                        }

                        $teacher = Db::name('pj_project_profile')
                            ->where('Translator', 'like', "%$name%")
                            ->whereOr('Reviser', 'like', "%$name%")
                            ->whereOr('Pre_Formatter', 'like', "%$name%")
                            ->whereOr('Post_Formatter', 'like', "%$name%")
                            ->field('id')
                            ->select();
                        $teacher = array_column($teacher,'id');
                        $id_arr = array_merge($id_arr,$teacher);
                        $id_arr = array_unique($id_arr);

                        $query = $this->where(function ($query) use ($id_arr) {
                            $query->where('id', 'in', $id_arr);

                        });



                    }else{
                        $query = $this->where(function ($query) use ($name) {
                            $query->where('Translator', 'like', "%$name%")
                                ->whereOr('Reviser', 'like', "%$name%")
                                ->whereOr('Pre_Formatter', 'like', "%$name%")
                                ->whereOr('Post_Formatter', 'like', "%$name%");
                        });
                    }


                }
                // codex 校长、副校长
            }else if(in_array($job_id, [4,15])) {

                $query = $this->where(function ($query) use($name) {
                    $query->where('Translator', 'like', "%$name%")
                        ->whereOr('Reviser', 'like', "%$name%");
                });

            }else if($job_id = 5) { // 培训专员

                $query = $this->where(function ($query) use($name) {
                    $query->where('Pre_Formatter', 'like', "%$name%")
                        ->whereOr('Post_Formatter', 'like', "%$name%");
                });

            }else{
                // 否则 就只显示自己录入的数据
                $where['Filled_by'] = $name;
            }
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
        return $query->where($where)->order('id desc')->paginate($limit);
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

    /*
     * 分职业获取 参与项目 文件列表
     * */
    public function getFileList()
    {
        $name = session('administrator')['name'];

        $job_id = session('administrator')['job_id'];

        $where = [];

        $query = $this;

            // 翻、校、排、助理
        if(in_array($job_id, [7,10,11,12,13,19])) {

            $field_name = '';

            /*if($job_id == 7){
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
            });*/

            if($job_id == 7){
                $field_name = 'PA';
                $query = $this->where(function ($query) use($field_name, $name) {
                    $query->where($field_name, 'like', "%$name%");
                });
            } else{
                //判断是否是老师
                $t = Db::name('pj_teacher_student')
                    ->where('delete_time',0)
                    ->where('pid',0)
                    ->where('name',$name)
                    ->find();
                if($t){

                    $stu = Db::name('pj_teacher_student')
                        ->where('delete_time', 0)
                        ->where('pid', $t['id'])
                        ->select();

                    $id_arr = [];
                    foreach($stu as $k3 => $v3){
                        $uname = $v3['name'];
                        $pn = Db::name('pj_project_profile')
                            ->where('Translator', 'like', "%$uname%")
                            ->whereOr('Reviser', 'like', "%$uname%")
                            ->whereOr('Pre_Formatter', 'like', "%$uname%")
                            ->whereOr('Post_Formatter', 'like', "%$uname%")
                            ->field('id')
                            ->select();
                        $arr2 = array_column($pn,'id');
                        $id_arr = array_merge($id_arr,$arr2);
                    }

                    $teacher = Db::name('pj_project_profile')
                        ->where('Translator', 'like', "%$name%")
                        ->whereOr('Reviser', 'like', "%$name%")
                        ->whereOr('Pre_Formatter', 'like', "%$name%")
                        ->whereOr('Post_Formatter', 'like', "%$name%")
                        ->field('id')
                        ->select();
                    $teacher = array_column($teacher,'id');
                    $id_arr = array_merge($id_arr,$teacher);
                    $id_arr = array_unique($id_arr);

                    $query = $this->where(function ($query) use ($id_arr) {
                        $query->where('id', 'in', $id_arr);

                    });



                }else{
                    $query = $this->where(function ($query) use ($name) {
                        $query->where('Translator', 'like', "%$name%")
                            ->whereOr('Reviser', 'like', "%$name%")
                            ->whereOr('Pre_Formatter', 'like', "%$name%")
                            ->whereOr('Post_Formatter', 'like', "%$name%");
                    });
                }
            }

            // codex 校长、副校长、质控主管
        }else if(in_array($job_id, [4,6,15])) {

            $query = $this->where(function ($query) use($name) {
                $query->where('Translator', 'like', "%$name%")
                    ->whereOr('Reviser', 'like', "%$name%");
            });

        }else if($job_id = 5) { // 培训专员

            $query = $this->where(function ($query) use($name) {
                $query->where('Pre_Formatter', 'like', "%$name%")
                    ->whereOr('Post_Formatter', 'like', "%$name%");
            });

        }else{
            // 否则 就只显示自己录入的数据
            $where['Filled_by'] = $name;
        }

        // 返回分页对象
        return $query->field('Project_Name')->where($where)->order('id desc')->select();
    }

}