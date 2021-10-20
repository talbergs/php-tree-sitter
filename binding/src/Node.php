<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;
use FFI;

final class Node
{
    public function __construct(public CData $data)
    {}

    /**
     * Get the node's type as a null-terminated string.
     */
    public function getType(): string
    {
        return API::ffi()->ts_node_type($this->data);
    }

    /**
     * Get the node's type as a numerical id.
     */
    public function getTypeID(): int
    {
        return API::ffi()->ts_node_symbol($this->data);
    }

    /**
     * Get the node's start byte.
     */
    public function getStartByte(): int
    {
        return API::ffi()->ts_node_start_byte($this->data);
    }

    /**
     * Get the node's start position in terms of rows and columns.
     */
    public function getStartPoint(): Point
    {
        return Point::new(
            API::ffi()->ts_node_start_point($this->data)
        );
    }

    /**
     * Get the node's end byte.
     */
    public function getEndByte(): int
    {
        return API::ffi()->ts_node_end_byte($this->data);
    }

    /**
     * Get the node's end position in terms of rows and columns.
     */
    public function getEndPoint(): Point
    {
        return Point::new(
            API::ffi()->ts_node_end_point($this->data)
        );
    }

    /**
     * Get an S-expression representing the node as a string.
     *
     * This string is allocated with `malloc` and the caller is responsible for
     * freeing it using `free`.
     */
    public function toString(): string
    {
        return FFI::string(
            API::ffi()->ts_node_string($this->data)
        );
    }

    /**
     * Check if the node is null. Functions like `ts_node_child` and
     * `ts_node_next_sibling` will return a null node to indicate that no such node
     * was found.
     */
    public function isNull(): bool
    {
        return API::ffi()->ts_node_is_null($this->data);
    }

    /**
     * Check if the node is *named*. Named nodes correspond to named rules in the
     * grammar, whereas *anonymous* nodes correspond to string literals in the
     * grammar.
     */
    public function isNamed(): bool
    {
        return API::ffi()->ts_node_is_named($this->data);
    }

    /**
     * Check if the node is *missing*. Missing nodes are inserted by the parser in
     * order to recover from certain kinds of syntax errors.
     */
    public function isMissing(): bool
    {
        return API::ffi()->ts_node_is_missing($this->data);
    }

    /**
     * Check if the node is *extra*. Extra nodes represent things like comments,
     * which are not required the grammar, but can appear anywhere.
     */
    public function isExtra(): bool
    {
        return API::ffi()->ts_node_is_extra($this->data);
    }

    /**
     * Check if a syntax node has been edited.
     */
    public function hasChanges(): bool
    {
        return API::ffi()->ts_node_has_changes($this->data);
    }

    /**
     * Check if the node is a syntax error or contains any syntax errors.
     */
    public function hasError(): bool
    {
        return API::ffi()->ts_node_has_error($this->data);
    }

    /**
     * Get the node's immediate parent.
     */
    public function getParent(): static
    {
        $node = new static(
            API::ffi()->ts_node_parent($this->data)
        );

        return $node;
    }

    /**
     * Get the node's child at the given index, where zero represents the first
     * child.
     */
    public function getChild(int $index): static
    {
        $node = new static(
            API::ffi()->ts_node_child($this->data, $index)
        );

        return $node;
    }

    /**
     * Get the node's number of children.
     */
    public function getChildCount(): int
    {
        $node = new static(
            API::ffi()->ts_node_child_count($this->data)
        );

        return $node;
    }

    /**
     * Get the node's *named* child at the given index.
     *
     * See also `ts_node_is_named`.
     */
    public function getNamedChild(int $index): static
    {
        $node = new static(
            API::ffi()->ts_node_named_child($this->data, $index)
        );

        return $node;
    }

    /**
     * Get the node's number of *named* children.
     *
     * See also `ts_node_is_named`.
     */
    public function getNamedChildCount(): int
    {
        return API::ffi()->ts_node_named_child_count($this->data);
    }

    /**
     * Get the node's child with the given field name.
     */
    public function getChildByFieldName(string $fieldName): static
    {
        $node = new static(
            API::ffi()->ts_node_child_by_field_name(
                $this->data,
                $fieldName,
                strlen($fieldName),
            )
        );

        return $node;
    }

    /**
     * Get the node's child with the given numerical field id.
     *
     * You can convert a field name to an id using the
     * `ts_language_field_id_for_name` function.
     */
    public function getChildByFieldID(int $fieldID): static
    {
        $node = new static(
            API::ffi()->ts_node_child_by_field_id(
                $this->data,
                $fieldID,
            )
        );

        return $node;
    }

    /**
     * Get the node's next sibling.
     */
    public function getNextSibling(): static
    {
        $node = new static(
            API::ffi()->ts_node_next_sibling($this->data)
        );

        return $node;
    }

    /**
     * Get the node's previous sibling.
     */
    public function getPrevSibling(): static
    {
        $node = new static(
            API::ffi()->ts_node_prev_sibling($this->data)
        );

        return $node;
    }

    /**
     * Get the node's next *named* sibling.
     */
    public function getNextNamedSibling(): static
    {
        $node = new static(
            API::ffi()->ts_node_next_named_sibling($this->data)
        );

        return $node;
    }

    /**
     * Get the node's previous *named* sibling.
     */
    public function getPrevNamedSibling(): static
    {
        $node = new static(
            API::ffi()->ts_node_prev_named_sibling($this->data)
        );

        return $node;
    }

    /**
     * Get the node's first child that extends beyond the given byte offset.
     */
    public function getFirstChildForByte(int $offset): static
    {
        $node = new static(
            API::ffi()->ts_node_first_child_for_byte($this->data, $offset)
        );

        return $node;
    }

    /**
     * Get the node's first named child that extends beyond the given byte offset.
     */
    public function getFirstNamedChildForByte(int $offset): static
    {
        $node = new static(
            API::ffi()->ts_node_first_named_child_for_byte($this->data, $offset)
        );

        return $node;
    }

    /**
     * Get the smallest node within this node that spans the given range of bytes.
     */
    public function getDescendantForByteRange(int $from, int $to): static
    {
        $node = new static(
            API::ffi()->ts_node_descendant_for_byte_range($this->data, $from, $to)
        );

        return $node;
    }

    /**
     * Get the smallest node within this node that spans the given range of
     * row, column positions.
     */
    public function getDescendantForPointRange(Point $from, Point $to): static
    {
        // TODO: Point has to be a C struct constructed at this side
        // a hepler in Pont::static.. (<- protected call)
        $node = new static(
            API::ffi()->ts_node_descendant_for_point_range($this->data, $from->data, $to->data)
        );

        return $node;
    }

    /**
     * Get the smallest named node within this node that spans the given range of bytes.
     */
    public function getNamedDescendantForByteRange(int $from, int $to): static
    {
        $node = new static(
            API::ffi()->ts_node_named_descendant_for_byte_range($this->data, $from, $to)
        );

        return $node;
    }

    /**
     * Get the smallest named node within this node that spans the given range of
     * row, column positions.
     */
    public function getNamedDescendantForPointRange(Point $from, Point $to): static
    {
        // TODO: Point has to be a C struct constructed at this side
        // a hepler in Pont::static.. (<- protected call)
        $node = new static(
            API::ffi()->ts_node_named_descendant_for_point_range($this->data, $from->data, $to->data)
        );

        return $node;
    }

    /**
     * Edit the node to keep it in-sync with source code that has been edited.
     *
     * This function is only rarely needed. When you edit a syntax tree with the
     * `ts_tree_edit` function, all of the nodes that you retrieve from the tree
     * afterward will already reflect the edit. You only need to use `ts_node_edit`
     * when you have a `TSNode` instance that you want to keep and continue to use
     * after an edit.
     */
    public function edit(InputEdit $edit): void
    {
        // TODO: InputEdit into C-struct
        // TODO: return nullable Nodes as this is idiomatic
        API::ffi()->ts_node_edit($this->data, $edit->data);
    }

    /**
     * Check if two nodes are identical.
     */
    public function eq(self $other): bool
    {
        return API::ffi()->ts_node_eq($this->data, $other->data);
    }

    /**
     * Extracts node's region from given source code.
     */
    public function text(string $source): string
    {
        return substr(
            $source,
            $this->getStartByte(),
            $this->getEndByte() - $this->getStartByte(),
        );
    }
}
