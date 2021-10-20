<?php

// Example shows how to update the tree when source code is updated.

declare(strict_types=1);

require(__DIR__ . '/vendor/autoload.php');

//                |- start byte
$source = '<?php $a = 123;$a = [];';
//                      |- old end byte
//
//                           |- new end byte
$sourceNew = '<?php $aa = 1234;$a = [];';

// One change in source code is described as one region of change.
//
// In this example we see, this region of change could also be
// described as two smaller consecutive regions of change:

// '<?php $a = 123;$a = [];';
//         ^- start byte
//         ^- old end byte
// '<?php $aa = 123;$a = [];';
//          ^- new end byte
//
// '<?php $aa = 123;$a = [];';
//                ^- start byte
//                ^- old end byte
// '<?php $aa = 1234;$a = [];';
//                 ^- new end byte

// If smaller regions are used, parser will be more performant, as it would
// only parse changed regions:

$parser = TS\Parser::new();
$parser->setLanguage(TS\Language\php());
$tree = $parser->parseString(null, $source);

$edit = new TS\InputEdit(
    startByte: 8,
    oldEndByte: 14,
    newEndByte: 19,
    startPoint: new TS\Point(1, 8),
    oldEndPoint: new TS\Point(1, 14),
    newEndPoint: new TS\Point(1, 19),
);

// A node references obtained before the edit will be updated also.
$roodNodeRef = $tree->getRootNode();

echo "Old tree + old source: ";
echo $roodNodeRef->text($source);
echo PHP_EOL;

echo "Old tree + new source: ";
echo $roodNodeRef->text($sourceNew);
echo PHP_EOL;

$tree->edit($edit);
echo "New tree + new source: ";
echo $roodNodeRef->text($sourceNew);
echo PHP_EOL;
