<?php

namespace okcoder\think\jwt\facade;
/**
 * Class Jwt
 */
class Jwt extends \think\Facade
{
    protected static function getFacadeClass()
    {
        return \okcoder\think\jwt\Jwt::class;
    }
}