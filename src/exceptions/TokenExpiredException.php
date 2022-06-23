<?php


namespace okcoder\think\jwt\exceptions;


class TokenExpiredException extends JwtException
{
    protected $code = 10004;

    protected $message = 'token已过期';
}