<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Tests\Data\Stubs\QueuedExport;
use Nikazooz\Simplesheet\Tests\Data\Stubs\SheetWith100Rows;
use Nikazooz\Simplesheet\Tests\TestCase;

class FromCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_from_collection()
    {
        $export = new SheetWith100Rows('A');

        $response = $export->store('from-collection-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__.'/../Data/Disks/Local/from-collection-store.xlsx', 'xlsx');

        $this->assertEquals($export->collection()->toArray(), $contents);

        // Cleanup
        unlink(__DIR__.'/../Data/Disks/Local/from-collection-store.xlsx');
    }

    /**
     * @test
     */
    public function can_export_with_multiple_sheets_from_collection()
    {
        $export = new QueuedExport();

        $response = $export->store('multiple-sheets-collection-store.xlsx');

        $this->assertTrue($response);

        $reader = $this->read(
            __DIR__.'/../Data/Disks/Local/multiple-sheets-collection-store.xlsx',
            'xlsx'
        );

        foreach ($export->sheets() as $sheetIndex => $sheet) {
            $worksheet = $this->getSheetByIndex($reader, $sheetIndex);

            $this->assertEquals($sheet->collection()->toArray(), array_values(iterator_to_array($worksheet->getRowIterator())));
            $this->assertEquals($sheet->title(), $worksheet->getName());
        }

        // Cleanup
        unlink(__DIR__.'/../Data/Disks/Local/multiple-sheets-collection-store.xlsx');
    }
}
