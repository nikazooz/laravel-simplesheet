<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;

class QueuedExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return \Nikazooz\Simplesheet\Tests\Data\Stubs\SheetWith100Rows[]
     */
    public function sheets(): array
    {
        return [
            new SheetWith100Rows('A'),
            new SheetWith100Rows('B'),
            new SheetWith100Rows('C'),
        ];
    }
}
