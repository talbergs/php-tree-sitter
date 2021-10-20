<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


class Query
{
    public function __construct(public CData $data)
    {}

    /**
     * Create a new query from a string containing one or more S-expression
     * patterns. The query is associated with a particular language, and can
     * only be run on syntax nodes parsed with that language.
     *
     * If all of the given patterns are valid, this returns a `TSQuery`.
     * If a pattern is invalid, this returns `NULL`, and provides two pieces
     * of information about the problem:
     * 1. The byte offset of the error is written to the `error_offset` parameter.
     * 2. The type of error is written to the `error_type` parameter.
     */
    public static function new(CData $language, string $query): static
    {
        $errorOffset = API::ffi()->new('uint32_t');
        $errorType = API::ffi()->new('TSQueryError');
        $data = API::ffi()->ts_query_new(
            API::ffi()->cast('TSLanguage *', $language),
            $query,
            strlen($query),
            \FFI::addr($errorOffset),
            \FFI::addr($errorType),
        );

        return new static($data);
    }

    /**
     * Delete a query, freeing all of the memory that it used.
     */
    public function __destruct()
    {
        API::ffi()->ts_query_delete($this->data);
    }

    /**
     * Get the number of patterns in the query.
     */
    public function getPatternCount(): int
    {
        return API::ffi()->ts_query_pattern_count($this->data);
    }
    /**
     * Get the number of captures in the query.
     */
    public function getCaptureCount(): int
    {
        return API::ffi()->ts_query_capture_count($this->data);
    }
    /**
     * Get the number of string literals in the query.
     */
    public function getStringCount(): int
    {
        return API::ffi()->ts_query_string_count($this->data);
    }

    /**
     * Get the byte offset where the given pattern starts in the query's source.
     *
     * This can be useful when combining queries by concatenating their source
     * code strings.
     */
    public function getStartByteForPattern(int $offset): int
    {
        return API::ffi()->ts_query_start_byte_for_pattern($this->data, $offset);
    }

    /**
     * Get all of the predicates for the given pattern in the query.
     *
     * The predicates are represented as a single array of steps. There are three
     * types of steps in this array, which correspond to the three legal values for
     * the `type` field:
     * - `TSQueryPredicateStepTypeCapture` - Steps with this type represent names
     *    of captures. Their `value_id` can be used with the
     *   `ts_query_capture_name_for_id` function to obtain the name of the capture.
     * - `TSQueryPredicateStepTypeString` - Steps with this type represent literal
     *    strings. Their `value_id` can be used with the
     *    `ts_query_string_value_for_id` function to obtain their string value.
     * - `TSQueryPredicateStepTypeDone` - Steps with this type are *sentinels*
     *    that represent the end of an individual predicate. If a pattern has two
     *    predicates, then there will be two steps with this `type` in the array.
     */
    public function getPredicatesForPattern(int $patternIndex): void
    {
        API::ffi()->ts_query_predicates_for_pattern($this->data, $patternIndex);
    }

    /**
     * Get all of the predicates for the given pattern in the query.
     *
     * The predicates are represented as a single array of steps. There are three
     * types of steps in this array, which correspond to the three legal values for
     * the `type` field:
     * - `TSQueryPredicateStepTypeCapture` - Steps with this type represent names
     *    of captures. Their `value_id` can be used with the
     *   `ts_query_capture_name_for_id` function to obtain the name of the capture.
     * - `TSQueryPredicateStepTypeString` - Steps with this type represent literal
     *    strings. Their `value_id` can be used with the
     *    `ts_query_string_value_for_id` function to obtain their string value.
     * - `TSQueryPredicateStepTypeDone` - Steps with this type are *sentinels*
     *    that represent the end of an individual predicate. If a pattern has two
     *    predicates, then there will be two steps with this `type` in the array.
     */
    public function stepIsDefinite(int $byteOffset): bool
    {
        return API::ffi()->ts_query_step_is_definite($this->data, $byteOffset);
    }

    /**
     * Get the name and length of one of the query's captures, or one of the
     * query's string literals. Each capture and string is associated with a
     * numeric id based on the order that it appeared in the query's source.
     */
    public function captureNameForID(int $id): string
    {
        return API::ffi()->ts_query_capture_name_for_id($this->data, $id);
    }

    /**
     * Get the name and length of one of the query's captures, or one of the
     * query's string literals. Each capture and string is associated with a
     * numeric id based on the order that it appeared in the query's source.
     */
    public function stringValueForID(int $id): string
    {
        return API::ffi()->ts_query_string_value_for_id($this->data, $id);
    }

    /**
     * Disable a certain capture within a query.
     *
     * This prevents the capture from being returned in matches, and also avoids
     * any resource usage associated with recording the capture. Currently, there
     * is no way to undo this.
     */
    public function disableCapture(string $capture): void
    {
        API::ffi()->ts_query_disable_capture($this->data, $capture);
    }

    /**
     * Disable a certain pattern within a query.
     *
     * This prevents the pattern from matching and removes most of the overhead
     * associated with the pattern. Currently, there is no way to undo this.
     */
    public function disablePattern(int $index): void
    {
        API::ffi()->ts_query_disable_pattern($this->data, $index);
    }
}
