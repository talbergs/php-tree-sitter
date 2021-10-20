<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


class QueryCursor
{
    public function __construct(private CData $data)
    {}

    /**
     * Create a new cursor for executing a given query.
     *
     * The cursor stores the state that is needed to iteratively search
     * for matches. To use the query cursor, first call `ts_query_cursor_exec`
     * to start running a given query on a given syntax node. Then, there are
     * two options for consuming the results of the query:
     * 1. Repeatedly call `ts_query_cursor_next_match` to iterate over all of the
     *    *matches* in the order that they were found. Each match contains the
     *    index of the pattern that matched, and an array of captures. Because
     *    multiple patterns can match the same set of nodes, one match may contain
     *    captures that appear *before* some of the captures from a previous match.
     * 2. Repeatedly call `ts_query_cursor_next_capture` to iterate over all of the
     *    individual *captures* in the order that they appear. This is useful if
     *    don't care about which pattern matched, and just want a single ordered
     *    sequence of captures.
     *
     * If you don't care about consuming all of the results, you can stop calling
     * `ts_query_cursor_next_match` or `ts_query_cursor_next_capture` at any point.
     *  You can then start executing another query on another node by calling
     *  `ts_query_cursor_exec` again.
     */
    public static function new(): static
    {
        return new static(API::ffi()->ts_query_cursor_new());
    }

    /**
     * Delete a query cursor, freeing all of the memory that it used.
     */
    public function delete(): void
    {
        API::ffi()->ts_query_cursor_delete($this->data);
    }

    /**
     * Start running a given query on a given node.
     */
    public function exec(Query $query, Node $node): void
    {
        API::ffi()->ts_query_cursor_exec($this->data, $query->data, $node->data);
    }

    /**
     * Check if this cursor has exceeded its maximum number of in-progress
     * matches.
     *
     * Currently, query cursors have a fixed capacity for storing lists
     * of in-progress captures. If this capacity is exceeded, then the
     * earliest-starting match will silently be dropped to make room for
     * further matches.
     */
    public function didExceedMatchLimit(): bool
    {
        return API::ffi()->ts_query_cursor_did_exceed_match_limit($this->data);
    }

    /**
     * Set the range of bytes in which the query
     * will be executed.
     */
    public function setByteRange(int $from, int $to): void
    {
        API::ffi()->ts_query_cursor_set_byte_range($this->data, $from, $to);
    }

    /**
     * Set the range of (row, column) positions in which the query
     * will be executed.
     */
    public function setPointRange(Point $from, Point $to): void
    {
        API::ffi()->ts_query_cursor_set_point_range($this->data, $from, $to);
    }

    /**
     * Advance to the next match of the currently running query.
     *
     * If there is a match, write it to `*match` and return `true`.
     * Otherwise, return `false`.
     */
    public function nextMatch(): ?QueryMatch
    {
        $match = API::ffi()->new('TSQueryMatch');
        $result = API::ffi()->ts_query_cursor_next_match($this->data, \FFI::addr($match));

        if ($result === false) {
            return null;
        }

        return new QueryMatch($match);
    }

    public function removeMatch(int $index): void
    {
        API::ffi()->ts_query_cursor_remove_match($this->data, $index);
    }

    /**
     * Advance to the next capture of the currently running query.
     *
     * If there is a capture, write its match to `*match` and its index within
     * the matche's capture list to `*capture_index`. Otherwise, return `false`.
     */
    public function nextCapture(): ?QueryCapture
    {
        static $match;
        static $captureIndex;
        if (!$match) {
            $match = API::ffi()->new('TSQueryMatch');
        }

        if (!$captureIndex) {
            $captureIndex = API::ffi()->new('uint32_t');
        }

        $result = API::ffi()->ts_query_cursor_next_capture(
            $this->data,
            \FFI::addr($match),
            \FFI::addr($captureIndex),
        );

        if ($result === false) {
            return null;
        }

        return new QueryCapture($match->captures[$captureIndex->cdata]);
    }
}
