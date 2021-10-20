<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


final class Tree
{
    // TODO: add readonly (php 8.1 feature) to these properties.
    public function __construct(public CData $data)
    {}

    /**
     * Create a shallow copy of the syntax tree. This is very fast.
     *
     * You need to copy a syntax tree in order to use it on more than one thread at
     * a time, as syntax trees are not thread safe.
     */
    public function copy(): static
    {
        return API::ffi()->ts_tree_copy($this->data);
    }

    /**
     * Delete the syntax tree, freeing all of the memory that it used.
     */
    public function delete(): void
    {
        API::ffi()->ts_tree_delete($this->data);
    }

    /**
     * Get the root node of the syntax tree.
     */
    public function getRootNode(): Node
    {
        return new Node(API::ffi()->ts_tree_root_node($this->data));
    }

    /**
     * Get the language that was used to parse the syntax tree.
     */
    public function getLanguage(): Language
    {
        return API::ffi()->ts_tree_language($this->data);
    }

    /**
     * Edit the syntax tree to keep it in sync with source code that has been
     * edited.
     *
     * You must describe the edit both in terms of byte offsets and in terms of
     * (row, column) coordinates.
     */
    public function edit(InputEdit $edit): void
    {
        API::ffi()->ts_tree_edit($this->data, \FFI::addr($edit->data));
    }

    /**
     * Compare an old edited syntax tree to a new syntax tree representing the same
     * document, returning an array of ranges whose syntactic structure has changed.
     *
     * For this to work correctly, the old syntax tree must have been edited such
     * that its ranges match up to the new tree. Generally, you'll want to call
     * this function right after calling one of the `ts_parser_parse` functions.
     * You need to pass the old tree that was passed to parse, as well as the new
     * tree that was returned from that function.
     *
     * The returned array is allocated using `malloc` and the caller is responsible
     * for freeing it using `free`. The length of the array will be written to the
     * given `length` pointer.
     */
    public function getChangedRanges(Tree $other): Range
    {
        return API::ffi()->ts_tree_get_changed_ranges($this->data, $other->data);
    }
}
