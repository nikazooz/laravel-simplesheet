<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use PHPUnit\Framework\Assert;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Validators\Failure;
use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Concerns\SkipsFailures;
use Nikazooz\Simplesheet\Concerns\SkipsOnFailure;
use Nikazooz\Simplesheet\Concerns\WithValidation;
use Nikazooz\Simplesheet\Concerns\WithBatchInserts;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;

class SkipsOnFailureTest extends TestCase
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
    public function can_skip_on_error()
    {
        $import = new class implements ToModel, WithValidation, SkipsOnFailure {
            use Importable;

            public $failures = 0;

            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model|null
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
                    '1' => Rule::in(['john@example.com']),
                ];
            }

            /**
             * @param  \Nikazooz\Simplesheet\Validators\Failure[]  $failures
             */
            public function onFailure(Failure ...$failures)
            {
                $failure = $failures[0];

                Assert::assertEquals(2, $failure->row());
                Assert::assertEquals('1', $failure->attribute());
                Assert::assertEquals(['The selected 1 is invalid.'], $failure->errors());
                Assert::assertEquals(['Jane Smith', 'jane@example.com'], $failure->values());

                $this->failures += \count($failures);
            }
        };

        $import->import('import-users.xlsx');

        $this->assertEquals(1, $import->failures);

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'jane@example.com',
        ]);
    }

    /**
     * @test
     */
    public function skips_only_failed_rows_in_batch()
    {
        $import = new class implements ToModel, WithValidation, WithBatchInserts, SkipsOnFailure {
            use Importable;

            public $failures = 0;

            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model|null
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
                    '1' => Rule::in(['john@example.com']),
                ];
            }

            /**
             * @param  \Nikazooz\Simplesheet\Validators\Failure[]  $failures
             */
            public function onFailure(Failure ...$failures)
            {
                $failure = $failures[0];

                Assert::assertEquals(2, $failure->row());
                Assert::assertEquals('1', $failure->attribute());
                Assert::assertEquals(['The selected 1 is invalid.'], $failure->errors());

                $this->failures += \count($failures);
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 100;
            }
        };

        $import->import('import-users.xlsx');

        $this->assertEquals(1, $import->failures);

        // Shouldn't have rollbacked/skipped the rest of the batch.
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'jane@example.com',
        ]);
    }

    /**
     * @test
     */
    public function can_skip_failures_and_collect_all_failures_at_the_end()
    {
        $import = new class implements ToModel, WithValidation, SkipsOnFailure {
            use Importable, SkipsFailures;

            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model|null
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
                    '1' => Rule::in(['john@example.com']),
                ];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(1, $import->failures());

        /** @var Failure $failure */
        $failure = $import->failures()->first();

        $this->assertEquals(2, $failure->row());
        $this->assertEquals('1', $failure->attribute());
        $this->assertEquals(['The selected 1 is invalid.'], $failure->errors());

        // Shouldn't have rollbacked other imported rows.
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        // Should have skipped inserting
        $this->assertDatabaseMissing('users', [
            'email' => 'jane@example.com',
        ]);
    }
}
