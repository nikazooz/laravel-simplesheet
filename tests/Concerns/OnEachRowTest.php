<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\OnEachRow;
use Nikazooz\Simplesheet\Concerns\Importable;

class OnEachRowTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_each_row_individually()
    {
        $import = new class implements OnEachRow {
            use Importable;

            public $called = 0;

            /**
             * @param  array  $row
             */
            public function onRow(array $row)
            {
                foreach ($row as $cell) {
                    Assert::assertEquals('test', $cell);
                }

                Assert::assertEquals([
                    'test', 'test',
                ], $row);

                $this->called++;
            }
        };

        $import->import('import.xlsx');

        $this->assertEquals(2, $import->called);
    }
}
