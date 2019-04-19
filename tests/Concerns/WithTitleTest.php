<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Tests\Data\Stubs\WithTitleExport;

class WithTitleTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_with_title()
    {
        $export = new WithTitleExport();

        $response = $export->store('with-title-store.xlsx');

        $this->assertTrue($response);

        $spreadsheet = $this->read(__DIR__.'/../Data/Disks/Local/with-title-store.xlsx', 'xlsx');

        $this->assertEquals('given-title', $this->getSheetByIndex($spreadsheet)->getName());
    }
}
