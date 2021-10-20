<?php

declare(strict_types=1);

use TS\Language;
use TS\Parser;


class PHPTest extends BaseCase
{
    public function test_basic()
    {
        $parser = Parser::new();
        $parser->setLanguage(Language\php());
        $tree = $parser->parseString(null, '<?php $var = "1";');

        $this->assertFalse($tree->getRootNode()->hasError());
    }
}
