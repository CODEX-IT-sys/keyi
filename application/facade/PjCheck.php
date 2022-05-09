<?php

namespace app\facade;

use think\facade;
use think\Model;

/**
 * 项目描述 模型的Facade
 * @package app\facade
 * @see     \app\common\model\PjCheck
 * @mixin \app\common\model\PjCheck
 */
class PjCheck extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\PjCheck';
    }
}