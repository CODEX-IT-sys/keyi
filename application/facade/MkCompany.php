<?php
namespace app\facade;

use think\facade;
use think\Model;

/**
 * 客户信息 模型的Facade
 * @package app\facade
 * @see     \app\common\model\MkCompany
 * @mixin \app\common\model\MkCompany
 */
class MkCompany extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\model\MkCompany';
    }
}