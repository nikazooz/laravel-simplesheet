<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Box\Spout\Common\Entity\Cell;
use Nikazooz\Simplesheet\Helpers\NumberFormat;
use Nikazooz\Simplesheet\Tests\Data\Stubs\WithColumnFormattingExport;
use Nikazooz\Simplesheet\Tests\TestCase;

class WithColumnFormattingTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_with_column_formatting()
    {
        $export = new WithColumnFormattingExport();

        $response = $export->store('with-column-formatting-store.xlsx');

        $this->assertTrue($response);

        $filePath = __DIR__.'/../Data/Disks/Local/with-column-formatting-store.xlsx';

        $reader = $this->read($filePath, 'xlsx');

        $sheet = $this->getSheetByIndex($reader);

        foreach ($sheet->getRowIterator() as $row) {
            /** @var Cell[] $cells */
            $cells = $row->getCells();
            $this->assertEquals($cells[1]->getStyle(), NumberFormat::FORMAT_NUMBER_00);
        }

        // Cleanup
        unlink(__DIR__.'/../Data/Disks/Local/with-column-formatting-store.xlsx');
    }
}
