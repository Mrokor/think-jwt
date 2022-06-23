<?php


namespace okcoder\think\jwt\parser;


use okcoder\think\jwt\contract\ParserContract;
use think\Request;

class Header implements ParserContract
{
    protected $header = 'authorization';

    protected $prefix = 'bearer';

    public function parse(Request $request)
    {
        $header = $request->header($this->header);
        if ($header && preg_match('/' . $this->prefix . '\s*(\S+)\b/i', $header, $matches)) {
            return $matches[1];
        }
    }

    public function setHeaderName($name)
    {
        $this->header = $name;

        return $this;
    }

    public function setHeaderPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }
}