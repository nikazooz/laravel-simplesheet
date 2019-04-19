<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Tests\Data\Stubs\SheetWith100Rows;

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
    }
}
