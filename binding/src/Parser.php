<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;
use FFI;


class Parser
{
    private function __construct(private FFI $ffi, private CData $data)
    {}

    /**
     * Delete the parser, freeing all of the memory that it used.
     */
    public function __destruct()
    {
        API::ffi()->ts_parser_delete($this->data);
    }

    /**
     * Create a new parser.
     */
    public static function new(): static
    {
        $data = API::ffi()->ts_parser_new();

        return new static(API::ffi(), $data);
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
    public function setLanguage(CData $language): bool
    {
        return API::ffi()->ts_parser_set_language(
            $this->data,
            API::ffi()->cast('TSLanguage *', $language),
        );
    }

    /**
     * Get the parser's current language.
     */
    public function getLanguage(): Language
    {
        return new Language(API::ffi()->ts_parser_language($this->data));
    }

    /**
     * Set the ranges of text that the parser should include when parsing.
     *
     * By default, the parser will always include entire documents. This function
     * allows you to parse only a *portion* of a document but still return a syntax
     * tree whose ranges match up with the document as a whole. You can also pass
     * multiple disjoint ranges.
     *
     * The second and third parameters specify the location and length of an array
     * of ranges. The parser does *not* take ownership of these ranges; it copies
     * the data, so it doesn't matter how these ranges are allocated.
     *
     * If `length` is zero, then the entire document will be parsed. Otherwise,
     * the given ranges must be ordered from earliest to latest in the document,
     * and they must not overlap. That is, the following must hold for all
     * `i` < `length - 1`:
     *
     *     ranges[i].end_byte <= ranges[i + 1].start_byte
     *
     * If this requirement is not satisfied, the operation will fail, the ranges
     * will not be assigned, and this function will return `false`. On success,
     * this function returns `true`
     *
     * @param Range[] $ranges
     */
    public function setIncludedRanges(array $ranges): bool
    {
        $size = count($ranges);
        $cranges = API::ffi()->new("TSRange[{$size}]");

        for ($i = 0; $i < $size; $i ++) {
            $range = $ranges[$i];
            $crange = API::ffi()->new("TSRange");

            $crange->start_point = $range->startPoint->data;
            $crange->end_point = $range->endPoint->data;
            $crange->start_byte = $range->startByte;
            $crange->end_byte = $range->endByte;

            $cranges[$i] = $crange;
        }

        return API::ffi()->ts_parser_set_included_ranges($this->data, $cranges, $size);
    }

    /**
     * Get the ranges of text that the parser will include when parsing.
     *
     * The returned pointer is owned by the parser. The caller should not free it
     * or write to it. The length of the array will be written to the given
     * `length` pointer.
     */
    public function getIncludedRanges(): Range
    {
        return API::ffi()->ts_parser_included_ranges($this->data);
    }

    /**
     * Use the parser to parse some source code and create a syntax tree.
     *
     * If you are parsing this document for the first time, pass `NULL` for the
     * `old_tree` parameter. Otherwise, if you have already parsed an earlier
     * version of this document and the document has since been edited, pass the
     * previous syntax tree so that the unchanged parts of it can be reused.
     * This will save time and memory. For this to work correctly, you must have
     * already edited the old syntax tree using the `ts_tree_edit` function in a
     * way that exactly matches the source code changes.
     *
     * The `TSInput` parameter lets you specify how to read the text. It has the
     * following three fields:
     * 1. `read`: A function to retrieve a chunk of text at a given byte offset
     *    and (row, column) position. The function should return a pointer to the
     *    text and write its length to the `bytes_read` pointer. The parser does
     *    not take ownership of this buffer; it just borrows it until it has
     *    finished reading it. The function should write a zero value to the
     *    `bytes_read` pointer to indicate the end of the document.
     * 2. `payload`: An arbitrary pointer that will be passed to each invocation
     *    of the `read` function.
     * 3. `encoding`: An indication of how the text is encoded. Either
     *    `TSInputEncodingUTF8` or `TSInputEncodingUTF16`.
     *
     * This function returns a syntax tree on success, and `NULL` on failure. There
     * are three possible reasons for failure:
     * 1. The parser does not have a language assigned. Check for this using the
          `ts_parser_language` function.
     * 2. Parsing was cancelled due to a timeout that was set by an earlier call to
     *    the `ts_parser_set_timeout_micros` function. You can resume parsing from
     *    where the parser left out by calling `ts_parser_parse` again with the
     *    same arguments. Or you can start parsing from scratch by first calling
     *    `ts_parser_reset`.
     * 3. Parsing was cancelled using a cancellation flag that was set by an
     *    earlier call to `ts_parser_set_cancellation_flag`. You can resume parsing
     *    from where the parser left out by calling `ts_parser_parse` again with
     *    the same arguments.
     */
    public function parse(?Tree $oldTree, Input $input): Tree
    {
        return API::ffi()->ts_parser_parse($this->data, $oldTree->data, $input->data);
    }

    /**
     * Use the parser to parse some source code stored in one contiguous buffer.
     * The first two parameters are the same as in the `ts_parser_parse` function
     * above. The second two parameters indicate the location of the buffer and its
     * length in bytes.
     */
    public function parseString(?Tree $oldTree, string $string): Tree
    {
        $tree = API::ffi()->ts_parser_parse_string(
            $this->data,
            $oldTree?->data,
            $string,
            strlen($string),
        );

        return new Tree($tree);
    }

    /**
     * Use the parser to parse some source code stored in one contiguous buffer with
     * a given encoding. The first four parameters work the same as in the
     * `ts_parser_parse_string` method above. The final parameter indicates whether
     * the text is encoded as UTF8 or UTF16.
     */
    public function parseStringEncoding(?Tree $oldTree, string $string, InputEncoding $encoding): Tree
    {
        return API::ffi()->ts_parser_parse_string_encoding($this->data, $oldTree, $string, strlen($string));
    }

    /**
     * Instruct the parser to start the next parse from the beginning.
     *
     * If the parser previously failed because of a timeout or a cancellation, then
     * by default, it will resume where it left off on the next call to
     * `ts_parser_parse` or other parsing functions. If you don't want to resume,
     * and instead intend to use this parser to parse some other document, you must
     * call `ts_parser_reset` first.
     */
    public function reset(): void
    {
        API::ffi()->ts_parser_reset($this->data);
    }

    /**
     * Set the maximum duration in microseconds that parsing should be allowed to
     * take before halting.
     *
     * If parsing takes longer than this, it will halt early, returning NULL.
     * See `ts_parser_parse` for more information.
     */
    public function setTimeoutMicros(int $ms): void
    {
        API::ffi()->ts_parser_set_timeout_micros($this->data, $ms);
    }

    /**
     * Get the duration in microseconds that parsing is allowed to take.
     */
    public function getTimeoutMicros(): int
    {
        return API::ffi()->ts_parser_timeout_micros($this->data);
    }

    /**
     * Set the parser's current cancellation flag pointer.
     *
     * If a non-null pointer is assigned, then the parser will periodically read
     * from this pointer during parsing. If it reads a non-zero value, it will
     * halt early, returning NULL. See `ts_parser_parse` for more information.
     */
    public function setCancelationFlag(int $flag): void
    {
        API::ffi()->ts_parser_set_cancellation_flag($this->data, $flag);
    }

    /**
     * Get the parser's current cancellation flag pointer.
     */
    public function getCancelationFlag(): int
    {
        return API::ffi()->ts_parser_cancellation_flag($this->data);
    }

    /**
     * Set the logger that a parser should use during parsing.
     *
     * The parser does not take ownership over the logger payload. If a logger was
     * previously assigned, the caller is responsible for releasing any memory
     * owned by the previous logger.
     */
    public function setLogger(Logger $logger): void
    {
        API::ffi()->ts_parser_set_logger($this->data, $logger);
    }

    /**
     * Get the parser's current logger.
     */
    public function getLogger(): Logger
    {
        return API::ffi()->ts_parser_logger($this->data);
    }

    /**
     * Set the file descriptor to which the parser should write debugging graphs
     * during parsing. The graphs are formatted in the DOT language. You may want
     * to pipe these graphs directly to a `dot(1)` process in order to generate
     * SVG output. You can turn off this logging by passing a negative number.
     */
    public function printDotGraphs(string $file): void
    {
        API::ffi()->ts_parser_print_dot_graphs($this->data);
    }
}
