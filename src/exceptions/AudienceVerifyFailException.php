<?php


namespace okcoder\think\jwt\exceptions;


class AudienceVerifyFailException extends JwtException
{
    protected $code = 10003;

    protected $message = '接收人验证失败';
}