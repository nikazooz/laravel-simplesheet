<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\WithHeadings;

class WithHeadingExport implements FromCollection, WithHeadings
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
     * @return array
     */
    public function headings(): array
    {
        return ['A', 'B', 'C'];
    }
}
