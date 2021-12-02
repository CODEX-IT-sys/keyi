<?php
namespace app\facade;

use think\facade;
use think\Model;

/**
 * Faq管理 模型的Facade
 */
class Faq extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\Faq';
    }
}