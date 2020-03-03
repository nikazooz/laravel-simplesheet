<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Concerns\WithBatchInserts;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\Group;

class QueuedImport implements ShouldQueue, ToModel, WithBatchInserts
{
    use Importable;

    /**
     * @param  array  $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Group([
            'name' => $row[0],
        ]);
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 50;
    }
}
