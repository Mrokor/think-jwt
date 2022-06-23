<?php

namespace okcoder\think\jwt\facade;
/**
 * Class Jwt
 * @method static \okcoder\think\jwt\Jwt create(integer|array|string $data, int $exp = 0)
 * @method static \okcoder\think\jwt\Jwt getToken()
 * @method static \okcoder\think\jwt\Jwt parse(string $token)
 * @method static \okcoder\think\jwt\Jwt logout(string $token)
 */
class Jwt extends \think\Facade
{
    protected static function getFacadeClass()
    {
        return \okcoder\think\jwt\Jwt::class;
    }
}