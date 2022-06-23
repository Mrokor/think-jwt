<?php


namespace okcoder\think\jwt\exceptions;


class SubjectVerifyFailException extends JwtException
{
    protected $code = 10006;

    protected $message = '主题验证失败';
}