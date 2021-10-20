<?php

declare(strict_types=1);

use TS\Language;
use TS\Parser;


class JavaScriptTest extends BaseCase
{
    public function test_basic()
    {
        $parser = Parser::new();
        $parser->setLanguage(Language\javascript());
        $tree = $parser->parseString(null, 'console.log(Math.random());');

        $this->assertFalse($tree->getRootNode()->hasError());
    }
}
