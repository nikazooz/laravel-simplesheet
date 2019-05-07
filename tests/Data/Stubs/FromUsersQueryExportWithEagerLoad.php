<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Nikazooz\Simplesheet\Concerns\FromQuery;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\WithMapping;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithEagerLoad implements FromQuery, WithMapping
{
    use Exportable;

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return User::query()->with([
            'groups' => function ($query) {
                $query->where('name', 'Group 1');
            },
        ])->withCount('groups');
    }

    /**
     * @param  mixed  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->groups_count,
            $row->groups->implode('name', ', '),
        ];
    }
}
