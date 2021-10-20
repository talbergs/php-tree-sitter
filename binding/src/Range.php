<?php

declare(strict_types=1);

namespace TS;


final class Range
{
    public function __construct(
        public Point $startPoint,
        public Point $endPoint,
        public int $startByte,
        public int $endByte
    ) {}
}
