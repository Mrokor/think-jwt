<?php


namespace okcoder\think\jwt\exceptions;


class IssuerVerifyFailException extends JwtException
{
    protected $code = 10002;

    protected $message = '签发人验证失败';
}