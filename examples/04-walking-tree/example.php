<?php

// You can access every node in a syntax tree using the Node APIs (Node::getNextSibling,.. etc.),
// but if you need to access a large number of nodes, the fastest way to do so is with a tree cursor.
// A cursor is a stateful object that allows you to walk a syntax tree with maximum efficiency.

declare(strict_types=1);

require(__DIR__ . '/vendor/autoload.php');

// In this example we print all leaf nodes along with some attributes.
$source = '<?php
$ham = new Ham();
function deepCloneHam(Ham $ham, bool $hammer): Ham {
    // Comment on $ham;
    return $ham->clone($hammer);
}';

$parser = TS\Parser::new();
$parser->setLanguage(TS\Language\php());
$tree = $parser->parseString(null, $source);
$cursor = TS\TreeCursor::new($tree->getRootNode());

$show = function (TS\Node $node) use ($source) {
    echo "\"\033[31;1;4m" . $node->text($source) . "\033[0m\"" . PHP_EOL;
    echo "\tType: " . $node->getType() . PHP_EOL;
    echo "\tTypeID: " . $node->getTypeID() . PHP_EOL;
    echo "\tIs Named: " . (int) $node->isNamed() . PHP_EOL;
    echo "\tIs Extra: " . (int) $node->isExtra() . PHP_EOL;
    echo "\tS-Expr: " . $node->toString() . PHP_EOL;
    echo "\tByte-range: " . $node->getStartByte() . " - " . $node->getEndByte() . PHP_EOL;
    echo PHP_EOL;
};

while (true) {
    dive: while ($cursor->gotoFirstChild());

    $show($cursor->getNode());

    sweep:
    if ($cursor->gotoNextSibling()) goto dive;
    if ($cursor->gotoParent()) goto sweep;

    break;
}
