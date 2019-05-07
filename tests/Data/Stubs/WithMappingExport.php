<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\WithMapping;
use Nikazooz\Simplesheet\Concerns\FromCollection;

class WithMappingExport implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            ['A1', 'B1', 'C1'],
            ['A2', 'B2', 'C2'],
        ]);
    }

    /**
     * @param  mixed  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            'mapped-'.$row[0],
            'mapped-'.$row[1],
            'mapped-'.$row[2],
        ];
    }
}
