<?php

namespace Nikazooz\Simplesheet\Concerns;

interface WithBatchInserts
{
    /**
     * @return int
     */
    public function batchSize(): int;
}
