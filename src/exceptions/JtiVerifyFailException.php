<?php


namespace okcoder\think\jwt\exceptions;


class JtiVerifyFailException extends JwtException
{
    protected $code = 10005;

    protected $message = '编号验证失败';
}