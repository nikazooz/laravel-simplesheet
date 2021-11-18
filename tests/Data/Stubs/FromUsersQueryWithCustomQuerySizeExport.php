<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\FromQuery;
use Nikazooz\Simplesheet\Concerns\WithCustomChunkSize;
use Nikazooz\Simplesheet\Concerns\WithCustomQuerySize;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;

class FromUsersQueryWithCustomQuerySizeExport implements FromQuery, WithCustomChunkSize, WithCustomQuerySize
{
    use Exportable;

    /**
     * @return Builder
     */
    public function query()
    {
        return User::query();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 10;
    }

    public function querySize(): int
    {
        return $this->query()->count();
    }
}
