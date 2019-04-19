<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Concerns\WithHeadingRow;
use Nikazooz\Simplesheet\Concerns\WithValidation;
use Nikazooz\Simplesheet\Concerns\WithBatchInserts;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use Nikazooz\Simplesheet\Validators\ValidationException;

class WithValidationTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(dirname(__DIR__).'/Data/Stubs/Database/Migrations');
    }

    /**
     * @test
     */
    public function can_validate_rows()
    {
        $import = new class implements ToModel, WithValidation {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name' => $row[0],
                    'email' => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'The selected 1 is invalid.',
            ]);

            $this->assertEquals([
                [
                    'There was an error on row 2. The selected 1 is invalid.',
                ],
            ], $e->errors());
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    /**
     * @test
     */
    public function can_validate_with_custom_attributes()
    {
        $import = new class implements ToModel, WithValidation {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name' => $row[0],
                    'email' => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            /**
             * @return array
             */
            public function customValidationAttributes()
            {
                return ['1' => 'email'];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    /**
     * @test
     */
    public function can_validate_with_custom_message()
    {
        $import = new class implements ToModel, WithValidation {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name' => $row[0],
                    'email' => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    '1' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }

            /**
             * @return array
             */
            public function customValidationMessages()
            {
                return [
                    '1.in' => 'Custom message for :attribute.',
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 2, '1', [
                'Custom message for 1.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    /**
     * @test
     */
    public function can_validate_rows_with_headings()
    {
        $import = new class implements ToModel, WithHeadingRow, WithValidation {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'email' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users-with-headings.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 3, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    /**
     * @test
     */
    public function can_validate_rows_in_batches()
    {
        $import = new class implements ToModel, WithHeadingRow, WithBatchInserts, WithValidation {
            use Importable;

            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
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
            public function batchSize(): int
            {
                return 2;
            }

            /**
             * @return array
             */
            public function rules(): array
            {
                return [
                    'email' => Rule::in(['patrick@maatwebsite.nl']),
                ];
            }
        };

        try {
            $import->import('import-users-with-headings.xlsx');
        } catch (ValidationException $e) {
            $this->validateFailure($e, 3, 'email', [
                'The selected email is invalid.',
            ]);
        }

        $this->assertInstanceOf(ValidationException::class, $e ?? null);
    }

    /**
     * @param  ValidationException  $e
     * @param  int  $row
     * @param  string  $attribute
     * @param  array  $messages
     * @return void
     */
    private function validateFailure(ValidationException $e, int $row, string $attribute, array $messages)
    {
        $failures = $e->failures();
        $failure = head($failures);

        $this->assertEquals($row, $failure->row());
        $this->assertEquals($attribute, $failure->attribute());
        $this->assertEquals($messages, $failure->errors());
    }
}
