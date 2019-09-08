<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\ToArray;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Concerns\SkipsUnknownSheets;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Nikazooz\Simplesheet\Exceptions\SheetNotFoundException;
use Nikazooz\Simplesheet\Tests\Data\Stubs\SheetWith100Rows;

class WithMultipleSheetsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../Data/Stubs/Database/Factories');
    }

    /**
     * @test
     */
    public function can_export_with_multiple_sheets_using_collections()
    {
        $export = new class implements WithMultipleSheets {
            use Exportable;

            /**
             * @return \Nikazooz\Simplesheet\Tests\Data\Stubs\SheetWith100Rows[]
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

        $export->store('multiple-sheets-collection-store.xlsx');

        $this->assertCount(100, $this->readAsArray(__DIR__.'/../Data/Disks/Local/multiple-sheets-collection-store.xlsx', 'xlsx', 0));
        $this->assertCount(100, $this->readAsArray(__DIR__.'/../Data/Disks/Local/multiple-sheets-collection-store.xlsx', 'xlsx', 1));
        $this->assertCount(100, $this->readAsArray(__DIR__.'/../Data/Disks/Local/multiple-sheets-collection-store.xlsx', 'xlsx', 2));

        // Cleanup
        unlink(__DIR__.'/../Data/Disks/Local/multiple-sheets-collection-store.xlsx');
    }

    /**
     * @test
     */
    public function unknown_sheet_index_will_throw_sheet_not_found_exception()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            /**
             * @return array
             */
            public function sheets(): array
            {
                return [
                    9999 => new class {
                    },
                ];
            }
        };

        $this->expectException(SheetNotFoundException::class);
        $this->expectExceptionMessage('Your requested sheet index: 9999 is out of bounds. The actual number of sheets is 2.');

        $import->import('import-multiple-sheets.xlsx');
    }

    /**
     * @test
     */
    public function unknown_sheet_name_will_throw_sheet_not_found_exception()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            /**
             * @return array
             */
            public function sheets(): array
            {
                return [
                    'Some Random Sheet Name' => new class {
                    },
                ];
            }
        };

        $this->expectException(SheetNotFoundException::class);
        $this->expectExceptionMessage('Your requested sheet name [Some Random Sheet Name] is out of bounds.');

        $import->import('import-multiple-sheets.xlsx');
    }

    /**
     * @test
     */
    public function unknown_sheet_name_can_be_ignored()
    {
        $import = new class implements WithMultipleSheets, SkipsUnknownSheets {
            use Importable;

            public $unknown;

            /**
             * @return array
             */
            public function sheets(): array
            {
                return [
                    'Some Random Sheet Name' => new class {
                    },
                ];
            }

            /**
             * @param  string|int  $sheetName
             */
            public function onUnknownSheet($sheetName)
            {
                $this->unknown = $sheetName;
            }
        };

        $import->import('import-multiple-sheets.xlsx');

        $this->assertEquals('Some Random Sheet Name', $import->unknown);
    }

    /**
     * @test
     */
    public function unknown_sheet_indices_can_be_ignored_per_name()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            public function sheets(): array
            {
                return [
                    'Some Random Sheet Name' => new class implements SkipsUnknownSheets {
                        /**
                         * @param  string|int  $sheetName
                         */
                        public function onUnknownSheet($sheetName)
                        {
                            Assert::assertEquals('Some Random Sheet Name', $sheetName);
                        }
                    },
                ];
            }
        };

        $import->import('import-multiple-sheets.xlsx');
    }

    /**
     * @test
     */
    public function unknown_sheet_indices_can_be_ignored()
    {
        $import = new class implements WithMultipleSheets, SkipsUnknownSheets {
            use Importable;

            public $unknown;

            public function sheets(): array
            {
                return [
                    99999 => new class {
                    },
                ];
            }

            /**
             * @param  string|int  $sheetName
             */
            public function onUnknownSheet($sheetName)
            {
                $this->unknown = $sheetName;
            }
        };

        $import->import('import-multiple-sheets.xlsx');

        $this->assertEquals(99999, $import->unknown);
    }

    /**
     * @test
     */
    public function unknown_sheet_indices_can_be_ignored_per_sheet()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            public function sheets(): array
            {
                return [
                    99999 => new class implements SkipsUnknownSheets {
                        /**
                         * @param string|int $sheetName
                         */
                        public function onUnknownSheet($sheetName)
                        {
                            Assert::assertEquals(99999, $sheetName);
                        }
                    },
                ];
            }
        };

        $import->import('import-multiple-sheets.xlsx');
    }

    /**
     * @test
     */
    public function can_import_multiple_sheets()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            public function sheets(): array
            {
                return [
                    new class implements ToArray {
                        public function array(array $array)
                        {
                            Assert::assertEquals([
                                ['1.A1', '1.B1'],
                                ['1.A2', '1.B2'],
                            ], $array);
                        }
                    },
                    new class implements ToArray {
                        public function array(array $array)
                        {
                            Assert::assertEquals([
                                ['2.A1', '2.B1'],
                                ['2.A2', '2.B2'],
                            ], $array);
                        }
                    },
                ];
            }
        };

        $import->import('import-multiple-sheets.xlsx');
    }

    /**
     * @test
     */
    public function can_import_multiple_sheets_by_sheet_name()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            public function sheets(): array
            {
                return [
                    'Sheet2' => new class implements ToArray {
                        public function array(array $array)
                        {
                            Assert::assertEquals([
                                ['2.A1', '2.B1'],
                                ['2.A2', '2.B2'],
                            ], $array);
                        }
                    },
                    'Sheet1' => new class implements ToArray {
                        public function array(array $array)
                        {
                            Assert::assertEquals([
                                ['1.A1', '1.B1'],
                                ['1.A2', '1.B2'],
                            ], $array);
                        }
                    },
                ];
            }
        };

        $import->import('import-multiple-sheets.xlsx');
    }

    /**
     * @test
     */
    public function can_import_multiple_sheets_by_sheet_index_and_name()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            public $sheets = [];

            public function __construct()
            {
                $this->sheets = [
                    0 => new class implements ToArray {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                            Assert::assertEquals([
                                ['1.A1', '1.B1'],
                                ['1.A2', '1.B2'],
                            ], $array);
                        }
                    },
                    'Sheet2' => new class implements ToArray {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                            Assert::assertEquals([
                                ['2.A1', '2.B1'],
                                ['2.A2', '2.B2'],
                            ], $array);
                        }
                    },
                ];
            }

            /**
             * @return array
             */
            public function sheets(): array
            {
                return $this->sheets;
            }
        };

        $import->import('import-multiple-sheets.xlsx');

        foreach ($import->sheets as $sheet) {
            $this->assertTrue($sheet->called);
        }
    }

    /**
     * @test
     */
    public function can_import_multiple_sheets_by_sheet_name_and_index()
    {
        $import = new class implements WithMultipleSheets {
            use Importable;

            public $sheets = [];

            public function __construct()
            {
                $this->sheets = [
                    'Sheet1' => new class implements ToArray {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                            Assert::assertEquals([
                                ['1.A1', '1.B1'],
                                ['1.A2', '1.B2'],
                            ], $array);
                        }
                    },
                    1 => new class implements ToArray {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                            Assert::assertEquals([
                                ['2.A1', '2.B1'],
                                ['2.A2', '2.B2'],
                            ], $array);
                        }
                    },
                ];
            }

            /**
             * @return array
             */
            public function sheets(): array
            {
                return $this->sheets;
            }
        };

        $import->import('import-multiple-sheets.xlsx');

        foreach ($import->sheets as $sheet) {
            $this->assertTrue($sheet->called);
        }
    }
}
