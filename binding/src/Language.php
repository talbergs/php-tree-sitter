<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


class Language
{
    public function __construct(public CData $data)
    {}

    public static function html(): static
    {
        $data = API::ffi()->tree_sitter_html();

        return new static($data);
    }

    public static function javascript(): static
    {
        $data = API::ffi()->tree_sitter_javascript();

        return new static($data);
    }

    public static function php(): static
    {
        $data = API::ffi2()->tree_sitter_php();

        return new static($data);
    }
}
