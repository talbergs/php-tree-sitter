<?php

// Example shows how to run queries and obtain results from them.

declare(strict_types=1);

require(__DIR__ . '/vendor/autoload.php');

$source = '<?php
    $a = [1,2,"3","4",5];
    $aaa = 123;
';

$parser = TS\Parser::new();
$parser->setLanguage(TS\Language\php());
$tree = $parser->parseString(null, $source);
$rootNode = $tree->getRootNode();

$query = TS\Query::new(TS\Language\php(), "
    ((array_element_initializer)@a)
    (((string)@b))
    ((expression_statement) @c)
    (assignment_expression left: (variable_name)@e right: (integer)@r)
");

$queryCursor = TS\QueryCursor::new();

// Match capture groups grouped in patterns.
$queryCursor->exec($query, $rootNode);
while ($match = $queryCursor->nextMatch()) {
    echo "PATTERN: {$match->patternIndex} ";
    foreach ($match->captures as $capture){
        echo "\n STR: " . $capture->node->text($source);
    }
    echo "\n";
}

echo "===============\n";

// Match only capture groups reggardless which pattern caught it.
$queryCursor->exec($query, $rootNode);
while ($capture = $queryCursor->nextCapture()) {
    echo "\n STR: " . $capture->node->text($source);
}
