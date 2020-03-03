<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Concerns\ToArray;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Concerns\WithHeadingRow;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use Nikazooz\Simplesheet\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithHeadingRowTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    /**
     * @test
     */
    public function can_import_each_row_to_model_with_heading_row()
    {
        $import = new class implements ToModel, WithHeadingRow {
            use Importable;

            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model
             */
            public function model(array $row): Model
            {
                return new User([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => 'secret',
                ]);
            }
        };

        $import->import('import-users-with-headings.xlsx');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);
    }

    /**
     * @test
     */
    public function can_import_each_row_to_model_with_different_heading_row()
    {
        $import = new class implements ToModel, WithHeadingRow {
            use Importable;

            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model
             */
            public function model(array $row): Model
            {
                return new User([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return int
             */
            public function headingRow(): int
            {
                return 4;
            }
        };

        $import->import('import-users-with-different-heading-row.xlsx');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);
    }

    /**
     * @test
     */
    public function can_import_to_array_with_heading_row()
    {
        $import = new class implements ToArray, WithHeadingRow {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ],
                    [
                        'name' => 'Jane Smith',
                        'email' => 'jane@example.com',
                    ],
                ], $array);
            }
        };

        $import->import('import-users-with-headings.xlsx');
    }
}
