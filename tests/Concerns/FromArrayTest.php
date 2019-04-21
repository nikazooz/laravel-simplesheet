<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\FromArray;
use Nikazooz\Simplesheet\Concerns\Exportable;

class FromArrayTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_from_array()
    {
        $export = new class implements FromArray {
            use Exportable;

            /**
             * @return array
             */
            public function array(): array
            {
                return [
                    ['test', 'test'],
                    ['test', 'test'],
                ];
            }
        };

        $response = $export->store('from-array-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__.'/../Data/Disks/Local/from-array-store.xlsx', 'xlsx');

        $this->assertEquals($export->array(), $contents);

        // Cleanup
        unlink(__DIR__.'/../Data/Disks/Local/from-array-store.xlsx');
    }
}
