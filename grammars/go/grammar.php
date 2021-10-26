<?php

declare(strict_types=1);

namespace TS\Language;

use FFI;
use FFI\CData;

function go(): CData
{
    static $ffi;

    if (!$ffi) {
        // https://clang.llvm.org/docs/CrossCompilation.html
        $arch = php_uname('m');
        $vendor = match (PHP_OS_FAMILY) {
            'Linux', 'BSD' => 'linux',
            'Darwin' => 'apple',
            'Windows' => 'w64',
        };
        $abi = match (PHP_OS_FAMILY) {
            'Linux', 'BSD' => 'gnu',
            'Darwin' => 'darwin',
            'Windows' => 'mingw32',
        };

        $bin = sprintf('%s-%s-%s.%s', $arch, $vendor, $abi, PHP_SHLIB_SUFFIX);

        $ffi = FFI::cdef(
            'typedef struct TSLanguage TSLanguage;'
            . PHP_EOL
            . 'TSLanguage *tree_sitter_go();',
            __DIR__ . '/' . $bin,
        );
    }

    return $ffi->tree_sitter_go();
}
