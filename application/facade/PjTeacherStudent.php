<?php

namespace app\facade;

use think\facade;
use think\Model;

/**
 * 来稿需求 模型的Facade
 * @package app\facade
 * @see     \app\common\model\PjTeacherStudent
 * @mixin \app\common\model\PjTeacherStudent
 */
class PjTeacherStudent extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\PjTeacherStudent';
    }
}