<?php

namespace okcoder\think\jwt\facade;
/**
 * Class Jwt
 * @method static string create(integer|array|string $data, int $exp = 0)
 * @method static string getToken()
 * @method static mixed parse(string $token)
 * @method static void logout(string $token)
 */
class Jwt extends \think\Facade
{
    protected static function getFacadeClass()
    {
        return \okcoder\think\jwt\Jwt::class;
    }
}