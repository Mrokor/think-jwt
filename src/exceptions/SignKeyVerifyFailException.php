<?php


namespace okcoder\think\jwt\exceptions;


class SignKeyVerifyFailException extends JwtException
{
    protected $code = 10007;

    protected $message = '签名密钥验证失败';
}