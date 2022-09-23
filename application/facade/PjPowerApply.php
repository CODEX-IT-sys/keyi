<?php

namespace app\facade;

use think\facade;
use think\Model;

/**
 * 项目汇总 模型的Facade
 * @package app\facade
 * @see     \app\common\model\PjPowerApply
 * @mixin \app\common\model\PjPowerApply
 */
class PjPowerApply extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\PjPowerApply';
    }
}