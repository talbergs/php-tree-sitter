<?php

declare(strict_types=1);

namespace TS;

use FFI\CData;


final class QueryMatch
{
    public int $id;
    public int $patternIndex;
    public int $captureCount;
    /** @var QueryCapture[] $captures */
    public array $captures;

    public function __construct(public CData $data)
    {
        $this->id = $data->id;
        $this->patternIndex = $data->pattern_index;
        $this->captureCount = $data->capture_count;

        $this->captures = [];
        foreach (range(0, $data->capture_count - 1) as $idx) {
            $this->captures[] = new QueryCapture($data->captures[$idx]);
        }
    }
}
