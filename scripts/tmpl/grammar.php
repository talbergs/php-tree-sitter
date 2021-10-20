<?php

declare(strict_types=1);

namespace TS\Language;

use FFI;
use FFI\CData;

function {{ $grammar }}(): CData
{
    static $ffi;

    if (!$ffi) {
        $ffi = FFI::cdef(
            'typedef struct TSLanguage TSLanguage;'
            . PHP_EOL
            . 'TSLanguage *tree_sitter_{{ $grammar }}();',
            __DIR__ . '/' . php_uname('m') . '.so',
        );
    }

    return $ffi->tree_sitter_{{ $grammar }}();
}
