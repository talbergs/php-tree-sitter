<?php

declare(strict_types=1);

require(__DIR__ . '/vendor/autoload.php');

// We will first parse as PHP and keep track of regions where text interpolation happen.
// Then these regions will be parsed using HTML parser and we will keep track of regions
// where we think javascript code would be. Then these regions are parsed using JavaScript
// parser. - Effectively leaving us with three independent Tree objects,
// for each of parsed languages.
// From there you can apply code analysis, refactor etc.
//
// In this example only variable names and tag attribute names will be printed per language.

$source = '
<html>
    <? foreach (range(0, 10) as $num1) { ?>
        <p bold="yes">#<?= $num1 + $num2 ?></p>
    <? } ?>

    <hr />

    <script delay>
        console.log(num3 + num4);
    </script>

    <script asycn>
        // console.log(num5);
    </script>

    <?php echo make_footer($footer, "<p italics>just a php string</p>"); ?>
</html>
';

$parser = TS\Parser::new();

// See, this source cannot immediately be parsed as HTML, if we do so, tree will have errors.
$parser->setLanguage(TS\Language\html());
$htmlTree = $parser->parseString(null, $source);
echo "Document as HTML has error 1 == " . (int) $htmlTree->getRootNode()->hasError() . PHP_EOL;

// Same with JabbaScript
$parser->setLanguage(TS\Language\javascript());
$jsTree = $parser->parseString(null, $source);
echo "Document as JavaScript has error 1 == " . (int) $jsTree->getRootNode()->hasError() . PHP_EOL;

$parser->setLanguage(TS\Language\php());
$phpTree = $parser->parseString(null, $source);
echo "Document as PHP has error 0 == " . (int) $phpTree->getRootNode()->hasError() . PHP_EOL;

/** @var TS\Range[] $htmlRanges */
$htmlRanges = [];
$queryPhpText = TS\Query::new(TS\Language\php(), '((text) @text)');
$queryCursor = TS\QueryCursor::new();
$queryCursor->exec($queryPhpText, $phpTree->getRootNode());
while ($capture = $queryCursor->nextCapture()) {
    $htmlRanges[] = new TS\Range(
        $capture->node->getStartPoint(),
        $capture->node->getEndPoint(),
        $capture->node->getStartByte(),
        $capture->node->getEndByte(),
    );
}

// Now only these text document ranges will be used to parse into HTML tree.
$parser->setIncludedRanges($htmlRanges);
$parser->setLanguage(TS\Language\html());
$htmlTree = $parser->parseString(null, $source);
echo "Document as PHP->HTML has error 0 == " . (int) $htmlTree->getRootNode()->hasError() . PHP_EOL;

// Now, the resulting HTML tree is queried for regions where to look for JabbaScript.
$jsRanges = [];
$queryScripts = TS\Query::new(TS\Language\html(), '(script_element (raw_text) @text)');
$queryCursor->exec($queryScripts, $htmlTree->getRootNode());
while ($capture = $queryCursor->nextCapture()) {
    $jsRanges[] = new TS\Range(
        $capture->node->getStartPoint(),
        $capture->node->getEndPoint(),
        $capture->node->getStartByte(),
        $capture->node->getEndByte(),
    );
}
$parser->setIncludedRanges($jsRanges);
$parser->setLanguage(TS\Language\javascript());
$jsTree = $parser->parseString(null, $source);
echo "Document as PHP->HTML->JavaScript has error 0 == " . (int) $jsTree->getRootNode()->hasError() . PHP_EOL;

// Let's query all of the resulting trees just to assert the correctness of parsing results.
$sugarQ = function (TS\Tree $tree, TS\Query $q) use ($source, $queryCursor) {
    $queryCursor->exec($q, $tree->getRootNode());
    while ($capture = $queryCursor->nextCapture()) {
        echo chr(9) . $capture->node->text($source) . PHP_EOL;
    }
};

echo "\n* HTML attribute names *\n";
$sugarQ($htmlTree, TS\Query::new(TS\Language\html(), '(attribute_name) @x'));

echo "\n* JavaScript variable names *\n";
$sugarQ($jsTree, TS\Query::new(TS\Language\javascript(), '(identifier) @x'));

echo "\n* PHP variable names *\n";
$sugarQ($phpTree, TS\Query::new(TS\Language\php(), '(variable_name) @x'));
