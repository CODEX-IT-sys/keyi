<?php
namespace app\facade;

use think\facade;
use think\Model;

/**
 * 任务预估 模型的Facade
 */
class Task extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\Task';
    }
}