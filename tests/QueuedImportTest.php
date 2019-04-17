<?php

namespace Nikazooz\Simplesheet\Tests;

use Nikazooz\Simplesheet\Concerns\Importable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Nikazooz\Simplesheet\Tests\Data\Stubs\QueuedImport;
use Nikazooz\Simplesheet\Tests\Data\Stubs\AfterQueueImportJob;

class QueuedImportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__ . '/Data/Stubs/Database/Migrations');
    }

    /**
     * @test
     */
    public function cannot_queue_import_that_does_not_implement_should_queue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Importable should implement ShouldQueue to be queued.');

        $import = new class {
            use Importable;
        };

        $import->queue('import-batches.xlsx');
    }

    /**
     * @test
     */
    public function can_queue_an_import()
    {
        $import = new QueuedImport();

        $chain = $import->queue('import-batches.xlsx')->chain([
            new AfterQueueImportJob(5000),
        ]);

        $this->assertInstanceOf(PendingDispatch::class, $chain);
    }
}
