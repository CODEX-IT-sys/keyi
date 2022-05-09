<?php
namespace app\common\model;

use think\Db;
use think\Exception;
use think\Model;
use think\model\concern\SoftDelete;
use think\Paginator;

// 系统管理员模型
class Admin extends Model
{
    // 软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    // 修改器-密码MD5
    protected function setPasswordAttr($value)
    {
        return md5($value);
    }

    // 工作状态 获取器
    public function getStatusAttr($value)
    {
        $status = [0=>'在职',1=>'离职'];
        return $status[$value];
    }

    // 实习生 获取器
    public function getTraineeAttr($value)
    {
        $trainee = [0=>'No',1=>'Yes'];
        return $trainee[$value];
    }

    // 所属部门 获取器
    public function getDepartmentIdAttr($value)
    {
        $bm = db('xt_department')->field('id, cn_name')->select();

        foreach ($bm as $k => $v)
        {
            $bm[$v['id']] = $v['cn_name'];
        }

        $departmentId = $bm;

        return $departmentId[$value];
    }

    // 所属职位 获取器
    public function getJobIdAttr($value)
    {

        $job_arr = db('xt_job')->field('id, cn_name')->select();

        foreach ($job_arr as $k => $v)
        {
            $job_arr[$v['id']] = $v['cn_name'];
        }

        $jobId = $job_arr;

        return $jobId[$value];
    }

    /**
     * 登录
     * @param string $name     用户名
     * @param string $password 密码
     * @return true|string 登录成功返回true，登录失败返回错误提示
     * @throws \think\exception\DbException
     */
    public function login($name, $password)
    {
        $admin = Db::name('admin')
            ->field(['id', 'password', 'name', 'status', 'job_id'])
            ->where('delete_time',0)
		    ->where('name', $name)->find();

        if ($admin === null) {
            return '账号不存在';
        }

        if ($admin['status'] != 0) {
            return '账号已被禁用';
        }

        if (md5($password) !== $admin['password']) {
            return '密码错误';
        }

        // 默认为中文版
        session('language','中文');
        unset($admin['password']);

        session('administrator', $admin);
        return true;
    }

    /**
     * 获取用户列表 (离职人员不显示)
     * @param   string|array        $field          搜索字段
     * @param   string              $keyword        关键词
     * @param   int                 $limit          每页显示条数
     * @return Paginator
     * @throws \think\exception\DbException
     */
    public function getList($field, $keyword, $limit = 50)
    {
        // 查询器对象    id为1的是默认超管 不允许操作
        $query = $this->where('id','<>', 1 )
            //->where('status',0)
            ->order('id desc');

        // 如果有关键词，添加查询条件
        if($field == 'job_id'){

            $query = $this->alias('a')
                ->join('xt_job j', 'a.job_id = j.id')
                ->field('a.*, j.cn_name')
                ->where('j.cn_name', 'like', "%$keyword%");

        }elseif ($field == 'department_id'){

            $query = $this->alias('a')
                ->join('xt_department j', 'a.department_id = j.id')
                ->field('a.*, j.cn_name')
                ->where('j.cn_name', 'like', "%$keyword%");

        }else{

            $query = $query->where($field, 'like', "%$keyword%");
        }

        // 返回分页对象
        return $query->paginate($limit);
    }

    /**
     * 获取管理员信息
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

    // 创建系统管理员
    public function createAdmin($data)
    {
        $allowField = ['name', 'password', 'email', 'phone', 'First_language',
            'department_id', 'job_id', 'entry_time', 'dimission_time', 'sign', 'trainee', 'status'];

        self::create($data, $allowField);
    }

    // 更新系统管理员
    public function updateAdmin($data,$id)
    {
        //$allowField = ['name', 'password', 'email', 'phone', 'department_id', 'job_id'];

        self::update($data, ['id' => $id]);
    }

    // 更新管理员的密码
    public function updatePassword($id, $password)
    {
        self::update(['password' => $password], ['id' => $id]);
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

    // 判断一个管理员是否存在
    public function checkId($id)
    {
        $id = $this->where('id', $id)->value('id');

        return $id !== null;
    }

    /**
     * 获取 翻译、校对、排版 用户列表(在职的)
     * @param   string|array        $field          搜索字段
     * @param   string              $keyword        关键词
     * @param   int                 $limit          每页显示条数
     * @return Paginator
     * @throws \think\exception\DbException
     */
    public function getOneList($field, $keyword, $limit = 50)
    {
        // 查询器对象
        $query = $this->where('job_id','in', [4,6,10,11,12,13,15,19])
            ->where('status',0)->order('id desc');

        // 如果有关键词，添加查询条件
        if ($keyword !== '') {
            $query = $query->where($field, 'like', "%$keyword%");
        }

        // 返回分页对象
        return $query->paginate($limit);
    }
}