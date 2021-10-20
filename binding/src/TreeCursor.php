<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


final class TreeCursor
{
    public function __construct(public CData $data)
    {}

    /**
     * Create a new tree cursor starting from the given node.
     *
     * A tree cursor allows you to walk a syntax tree more efficiently than is
     * possible using the `TSNode` functions. It is a mutable object that is always
     * on a certain syntax node, and can be moved imperatively to different nodes.
     */
    public static function new(Node $node): static
    {
        return new self(API::ffi()->ts_tree_cursor_new($node->data));
    }

    /**
     * Re-initialize a tree cursor to start at a different node.
     */
    public function reset(Node $node): void
    {
        API::ffi()->ts_tree_cursor_reset($this->data, $node->data);
    }

    /**
     * Get the tree cursor's current node.
     */
    public function getNode(): Node
    {
        return new Node(API::ffi()->ts_tree_cursor_current_node(\FFI::addr($this->data)));
    }

    /**
     * Get the field name of the tree cursor's current node.
     *
     * This returns `NULL` if the current node doesn't have a field.
     * See also `ts_node_child_by_field_name`.
     */
    public function getCurrentFieldName(): ?string
    {
        $name = API::ffi()->ts_tree_cursor_current_field_name(\FFI::addr($this->data));

        return $name;
    }

    /**
     * Get the field name of the tree cursor's current node.
     *
     * This returns zero if the current node doesn't have a field.
     * See also `ts_node_child_by_field_id`, `ts_language_field_id_for_name`.
     */
    public function getCurrentFieldID(): int
    {
        return API::ffi()->ts_tree_cursor_current_field_id(\FFI::addr($this->data));
    }

    /**
     * Move the cursor to the parent of its current node.
     *
     * This returns `true` if the cursor successfully moved, and returns `false`
     * if there was no parent node (the cursor was already on the root node).
     */
    public function gotoParent(): bool
    {
        return API::ffi()->ts_tree_cursor_goto_parent(\FFI::addr($this->data));
    }

    /**
     * Move the cursor to the next sibling of its current node.
     *
     * This returns `true` if the cursor successfully moved, and returns `false`
     * if there was no next sibling node.
     */
    public function gotoNextSibling(): bool
    {
        return API::ffi()->ts_tree_cursor_goto_next_sibling(\FFI::addr($this->data));
    }

    /**
     * Move the cursor to the first child of its current node.
     *
     * This returns `true` if the cursor successfully moved, and returns `false`
     * if there were no children.
     */
    public function gotoFirstChild(): bool
    {
        return API::ffi()->ts_tree_cursor_goto_first_child(\FFI::addr($this->data));
    }

    /**
     * Move the cursor to the first child of its current node that extends beyond
     * the given byte offset.
     *
     * This returns the index of the child node if one was found, and returns -1
     * if no such child was found.
     */
    public function gotoFirstChildForByte(int $byteOffset): int
    {
        return API::ffi()->ts_tree_cursor_goto_first_child_for_byte(
            \FFI::addr($this->data),
            $byteOffset,
        );
    }

    public function copy(): static
    {
        $data = API::ffi()->ts_tree_cursor_copy(\FFI::addr($this->data));

        return new self($data);
    }

    public function __destruct()
    {
        // API::ffi()->ts_tree_cursor_delete($this->data);
    }
}
