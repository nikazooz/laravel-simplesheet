<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\ToArray;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use Nikazooz\Simplesheet\Tests\Data\Stubs\SheetWith100Rows;
use Nikazooz\Simplesheet\Tests\Data\Stubs\SheetForUsersFromView;

class WithMultipleSheetsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
    }

    /**
     * @test
     */
    public function can_export_with_multiple_sheets_using_collections()
    {
        $export = new class implements WithMultipleSheets {
            use Exportable;

            /**
             * @return SheetWith100Rows[]
             */
            public function sheets() : array
            {
                return [
                    new SheetWith100Rows('A'),
                    new SheetWith100Rows('B'),
                    new SheetWith100Rows('C'),
                ];
            }
        };

        $export->store('from-view.xlsx');

        $this->assertCount(100, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'xlsx', 0));
        $this->assertCount(100, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'xlsx', 1));
        $this->assertCount(100, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'xlsx', 2));
    }
}
