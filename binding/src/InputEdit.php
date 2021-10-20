<?php

declare(strict_types=1);

namespace TS;


final class InputEdit
{
    public function __construct(
        public int $startByte,
        public int $oldEndByte,
        public int $newEndByte,
        public Point $startPoint,
        public Point $oldEndPoint,
        public Point $newEndPoint,
    ) {
        $this->data = API::ffi()->new('TSInputEdit');
        $this->data->start_byte = $startByte;
        $this->data->old_end_byte = $oldEndByte;
        $this->data->new_end_byte = $newEndByte;
        $this->data->start_point = $startPoint->data;
        $this->data->old_end_point = $oldEndPoint->data;
        $this->data->new_end_point = $newEndPoint->data;
    }
}
