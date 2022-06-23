<?php


namespace okcoder\think\jwt\exceptions;


class TokenLogoutOffException extends JwtException
{
    protected $code = 10000;

    protected $message = '该token已经注销';
}