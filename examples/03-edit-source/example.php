<?php

// Example shows how to create and apply source code edits that are inferred from intended node changes.
// Basically this is a reverse of 02 example.

declare(strict_types=1);

require(__DIR__ . '/vendor/autoload.php');

// In this example we attempt to rename part of document variables from "ham" into "chicken".
$source = '<?php
$ham = new Ham();
function deepCloneHam(Ham $ham, bool $hammer): Ham {
    // Comment on $ham;
    return $ham->clone($hammer);
}';
// An attept to rename variable only within function scope should result in such source code:
$sourceExpected = '<?php
$ham = new Ham();
function deepCloneHam(Ham $chicken, bool $hammer): Ham {
    // Comment on $ham;
    return $chicken->clone($hammer);
}';

// Algorithm would be:
// 1. parse the source
// 2. query for scope node (the function scope)
// 3. query for specific variable within this scope
// 4. edit source at region of this node
// 5. sync tree with source (apply input edit to tree)
// 6. repeat 4-5 as long as needed (this simple refactoring would not require rerun the query)

$parser = TS\Parser::new();
$parser->setLanguage(TS\Language\php());
$tree = $parser->parseString(null, $source);
$queryCursor = TS\QueryCursor::new();

$queryScope = TS\Query::new(TS\Language\php(), '
    (function_definition) @fdef
');
$queryCursor->exec($queryScope, $tree->getRootNode());
$scopeNode = $queryCursor->nextCapture()->node;

$queryVariable = TS\Query::new(TS\Language\php(), '
    ((variable_name (name) @name) (#eq? @name "ham"))
');
$queryCursor->exec($queryVariable, $scopeNode);

while ($capture = $queryCursor->nextCapture()) {
    /*
     * Note - Predicates are not handled directly by the Tree-sitter C library.
     * They are just exposed in a structured form so that higher-level code can perform the filtering.
     * However, higher-level bindings to Tree-sitter like the Rust crate or the WebAssembly binding implement a
     * predicates like #eq? and #match?.
     */
    if ($capture->node->text($source) !== "ham") {
        continue;
    }

    // (4)
    $source = substr($source, 0, $capture->node->getStartByte())
        . 'chicken' . substr($source, $capture->node->getEndByte());

    // In such implementation an edit to the source must immediately
    // be reflected into tree, else, since next capture still holds old region of node,
    // and in combination with new source, conditional would be wrong as node's text is out of sync.

    // (5)
    $edit = new TS\InputEdit(
        startByte: $capture->node->getStartByte(),
        oldEndByte: $capture->node->getEndByte(),
        newEndByte: $capture->node->getEndByte() + (strlen("chicken") - strlen("ham")),
        startPoint: $capture->node->getStartPoint(),
        oldEndPoint: $capture->node->getEndPoint(),
        newEndPoint: new TS\Point(
            $capture->node->getEndPoint()->row,
            $capture->node->getEndPoint()->column + (strlen("chicken") - strlen("ham")),
        ),
    );
    $tree->edit($edit);
}

echo $source . PHP_EOL . '--' . PHP_EOL;
var_dump($source === $sourceExpected);
