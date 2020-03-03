<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\WithHeadings;
use Nikazooz\Simplesheet\Tests\TestCase;

class WithHeadingsTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        unlink(__DIR__.'/../Data/Disks/Local/with-heading-store.xlsx');
    }

    /**
     * @test
     */
    public function can_export_from_collection_with_heading_row()
    {
        $export = new class implements FromCollection, WithHeadings {
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
        };

        $response = $export->store('with-heading-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__.'/../Data/Disks/Local/with-heading-store.xlsx', 'xlsx');

        $expected = [
            ['A', 'B', 'C'],
            ['A1', 'B1', 'C1'],
            ['A2', 'B2', 'C2'],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function can_export_from_collection_with_multiple_heading_rows()
    {
        $export = new class implements FromCollection, WithHeadings {
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
                return [
                    ['A', 'B', 'C'],
                    ['Aa', 'Bb', 'Cc'],
                ];
            }
        };

        $response = $export->store('with-heading-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__.'/../Data/Disks/Local/with-heading-store.xlsx', 'xlsx');

        $expected = [
            ['A', 'B', 'C'],
            ['Aa', 'Bb', 'Cc'],
            ['A1', 'B1', 'C1'],
            ['A2', 'B2', 'C2'],
        ];

        $this->assertEquals($expected, $actual);
    }
}
