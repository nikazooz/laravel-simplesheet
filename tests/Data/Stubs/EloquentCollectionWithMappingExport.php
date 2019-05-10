<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Collection;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\WithMapping;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;

class EloquentCollectionWithMappingExport implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            new User([
                'firstname' => 'John',
                'lastname' => 'Doe',
            ]),
        ]);
    }

    /**
     * @param  \Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User  $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->firstname,
            $user->lastname,
        ];
    }
}
