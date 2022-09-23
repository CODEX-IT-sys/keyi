<?php

namespace app\facade;

use think\facade;
use think\Model;

/**
 * Faq管理 模型的Facade
 */
class PjApproval extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\PjApproval';
    }
}