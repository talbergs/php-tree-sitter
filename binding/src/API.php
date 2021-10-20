<?php

declare(strict_types=1);

namespace TS;

use FFI;
use FFI\CData;

class API
{
    protected FFI $ffi; // readonly 
    protected static ?API $instance = null;

    public static function queryError(): CData
    {
        return FFI::new('TSQueryError');
    }

    public static function ffi(): FFI
    {
        static $ffi;

        if (!$ffi) {
            $ffi = FFI::cdef(
                file_get_contents(__DIR__ . '/../builds/header.h'),
                __DIR__ . '/../builds/' . php_uname('m') . '.so',
            );
        }

        return $ffi;
    }

    public static function api(): static
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->ffi = FFI::load(__DIR__ . '/../header.h');
        }

        return self::$instance;
    }

    /**
     * Create a new parser.
     */
    public function parserNew(): CData
    {
        return $this->ffi->ts_parser_new();
    }

    /**
     * Set the language that the parser should use for parsing.
     *
     * Returns a boolean indicating whether or not the language was successfully
     * assigned. True means assignment succeeded. False means there was a version
     * mismatch: the language was generated with an incompatible version of the
     * Tree-sitter CLI. Check the language's version using `ts_language_version`
     * and compare it to this library's `TREE_SITTER_LANGUAGE_VERSION` and
     * `TREE_SITTER_MIN_COMPATIBLE_LANGUAGE_VERSION` constants.
     */
    public function parserSetLanguage(CData $parser, CData $language): bool
    {
        return $this->ffi->ts_parser_set_language($parser, $language);
    }

    /**
     * Use the parser to parse some source code stored in one contiguous buffer.
     * The first two parameters are the same as in the `ts_parser_parse` function
     * above. The second two parameters indicate the location of the buffer and its
     * length in bytes.
     */
    public function parserParseString(CData $parser, ?CData $oldTree, string $string, int $length): CData
    {
        return $this->ffi->ts_parser_parse_string($parser, $oldTree, $string, $length);
    }

    /**
     * Get the root node of the syntax tree.
     */
    public function treeRootNode(CData $tree): CData
    {
        return $this->ffi->ts_tree_root_node($tree);
    }

    /**
     * Get an S-expression representing the node as a string.
     *
     * This string is allocated with `malloc` and the caller is responsible for
     * freeing it using `free`.
     */
    public function nodeString(CData $node): CData
    {
        return $this->ffi->ts_node_string($node);
    }

    public function treeSitterPhp(): CData
    {
        return $this->ffi->tree_sitter_php();
    }
}
