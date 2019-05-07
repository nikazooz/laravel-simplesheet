<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Simplesheet;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\ToArray;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\WithCustomCsvSettings;

class WithCustomCsvSettingsTest extends TestCase
{
    /**
     * @var Simplesheet
     */
    protected $SUT;

    protected function setUp(): void
    {
        parent::setUp();

        $this->SUT = $this->app->make(Simplesheet::class);
    }

    /**
     * @test
     */
    public function can_store_csv_export_with_custom_settings()
    {
        $export = new class implements FromCollection, WithCustomCsvSettings {
            /**
             * @return \Illuminate\Support\Collection
             */
            public function collection()
            {
                return collect([
                    ['A1', 'B1'],
                    ['A2', 'B2'],
                ]);
            }

            /**
             * @return array
             */
            public function getCsvSettings(): array
            {
                return [
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'line_ending' => PHP_EOL,
                    'use_bom' => true,
                    'include_separator_line' => true,
                    'excel_compatibility' => false,
                ];
            }
        };

        $this->SUT->store($export, 'custom-csv.csv');

        $contents = file_get_contents(__DIR__.'/../Data/Disks/Local/custom-csv.csv');

        $this->assertStringContainsString('sep=;', $contents);
        $this->assertStringContainsString('A1;B1', $contents);
        $this->assertStringContainsString('A2;B2', $contents);
    }

    /**
     * @test
     */
    public function can_read_csv_import_with_custom_settings()
    {
        $import = new class implements WithCustomCsvSettings, ToArray {
            /**
             * @return array
             */
            public function getCsvSettings(): array
            {
                return [
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'escape_character' => '\\',
                    'contiguous' => true,
                    'input_encoding' => 'UTF-8',
                ];
            }

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['A1', 'B1'],
                    ['A2', 'B2'],
                ], $array);
            }
        };

        $this->SUT->import($import, 'csv-with-other-delimiter.csv');
    }

    /**
     * @test
     */
    public function cannot_read_with_wrong_delimiter()
    {
        $import = new class implements WithCustomCsvSettings, ToArray {
            /**
             * @return array
             */
            public function getCsvSettings(): array
            {
                return [
                    'delimiter' => ',',
                ];
            }

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['A1;B1'],
                    ['A2;B2'],
                ], $array);
            }
        };

        $this->SUT->import($import, 'csv-with-other-delimiter.csv');
    }
}
