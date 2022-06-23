<?php


namespace okcoder\think\jwt\exceptions;


use Exception;

class JwtException extends Exception
{
    protected $code = -1;

    protected $message = 'JWT错误';
}