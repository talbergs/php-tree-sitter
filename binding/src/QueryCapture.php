<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


final class QueryCapture
{
    public Node $node;
    public int $index;

    public function __construct(public CData $data)
    {
        $this->node = new Node($data->node);
        $this->index = $data->index;
    }
}
