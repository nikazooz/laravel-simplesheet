<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\FromQuery;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Concerns\WithMapping;
use Nikazooz\Simplesheet\Events\BeforeSheet;
use Nikazooz\Simplesheet\Concerns\WithCustomQuerySize;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithMappingAndCustomQuerySize implements FromQuery, WithMapping, WithEvents, WithCustomQuerySize
{
    use Exportable;

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return User::query();
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->sheet->chunkSize(10);
            },
        ];
    }

    /**
     * @param  \Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            'name' => $row->name,
        ];
    }

    public function querySize(): int
    {
        return $this->query()->count();
    }
}
