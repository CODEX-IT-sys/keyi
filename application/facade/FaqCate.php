<?php
namespace app\facade;

use think\facade;
use think\Model;

/**
 * Faq分类 模型的Facade
 */
class FaqCate extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\FaqCate';
    }
}