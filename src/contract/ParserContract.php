<?php

namespace okcoder\think\jwt\contract;

use think\Request;

interface ParserContract
{
    public function parse(Request $request);
}