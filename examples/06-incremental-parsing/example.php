<?php

declare(strict_types=1);

require(__DIR__ . '/vendor/autoload.php');

// The salt in tree-sitter library is it's ability to do incremental parsing,
// yielding pretty pleasant performance.

// ~20k lines of php code.
$source = file_get_contents(__DIR__ . '/fixture-code');
$bytes = strlen($source);
$lines = count(explode("\n", $source));

$parser = TS\Parser::new();
$parser->setLanguage(TS\Language\php());

$before = microtime(true);
$tree = $parser->parseString(null, $source);
$after = microtime(true);
$time = round(($after - $before) * 1000, 3);

echo "{$time} milliseconds spent for initial parsing of source file ({$bytes} bytes / {$lines} lines)\n";

// We will edit the source programmatically, although in real
// life this would most likely be an text editor induced patch,
// already having all the correct ranges and byte offsets.
$query = TS\Query::new(TS\Language\php(), "(method_declaration (name) @cap)");
$queryCursor = TS\QueryCursor::new();

$lookingFor = 'theSpecialMethodToBeRenamed';
$toBeNamed = strrev($lookingFor); // to ease the example the edit is such that does not change region
$queryCursor->exec($query, $tree->getRootNode());
while ($capture = $queryCursor->nextCapture()) {
    if ($capture->node->text($source) === $lookingFor) {
        $tree->edit(new TS\InputEdit(
            startByte: $capture->node->getStartByte(),
            oldEndByte: $capture->node->getEndByte(),
            newEndByte: $capture->node->getEndByte(),
            startPoint: $capture->node->getStartPoint(),
            oldEndPoint: $capture->node->getEndPoint(),
            newEndPoint: $capture->node->getEndPoint(),
        ));
        break;
    }
}

$source = strtr($source, [$lookingFor => $toBeNamed]);

$before = microtime(true);
$tree = $parser->parseString($tree, $source);
$after = microtime(true);
$time = round(($after - $before) * 1000, 3);

echo "{$time} milliseconds spent in incremental parsing on the newly edited file\n";
