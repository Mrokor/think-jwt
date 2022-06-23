<?php


namespace okcoder\think\jwt\exceptions;


class TokenParseFailException extends JwtException
{
    protected $code = 10001;

    protected $message = 'token解码失败';
}