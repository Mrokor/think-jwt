<?php


namespace okcoder\think\jwt\parser;

use okcoder\think\jwt\contract\ParserContract;
use think\Request;

class Param implements ParserContract
{
    use KeyTrait;

    public function parse(Request $request)
    {
        return $request->param($this->key);
    }
}
