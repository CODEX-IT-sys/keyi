<?php

namespace app\facade;

use think\facade;
use think\Model;

/**
 * 术语库记录 模型的Facade
 * @package app\facade
 * @see     \app\common\model\PjTermRecord
 * @mixin \app\common\model\PjTermRecord
 */
class PjTermRecord extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\PjTermRecord';
    }
}