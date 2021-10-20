<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


final class Point
{
    public CData $data;

    public function __construct(
        public int $row,
        public int $column
    ) {
        $this->data = API::ffi()->new('TSPoint');
        $this->data->row = $row;
        $this->data->column = $column;
    }

    public static function new(CData $data): static
    {
        return new static($data->row, $data->column);
    }
}
