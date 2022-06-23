<?php


namespace okcoder\think\jwt\parser;

use okcoder\think\jwt\contract\ParserContract;
use think\Request;

class Cookie implements ParserContract
{
    use KeyTrait;

    public function parse(Request $request)
    {
        return \think\facade\Cookie::get($this->key);
    }
}
