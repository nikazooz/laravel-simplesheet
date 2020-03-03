<?php

namespace Nikazooz\Simplesheet\Tests;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Files\RemoteTemporaryFile;
use Nikazooz\Simplesheet\Jobs\QueueImport;
use Nikazooz\Simplesheet\Tests\Data\Stubs\AfterQueueImportJob;
use Nikazooz\Simplesheet\Tests\Data\Stubs\QueuedImport;

class QueuedImportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__.'/Data/Stubs/Database/Migrations');
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

    /**
     * @test
     */
    public function can_queue_import_with_remote_temp_disk()
    {
        config()->set('simplesheet.temporary_files.remote_disk', 'test');

        // Delete the local temp file before each read chunk job
        // to simulate using a shared remote disk, without
        // having a dependency on a local temp file.
        Queue::before(function (JobProcessing $event) {
            if ($event->job->resolveName() === QueueImport::class) {
                /** @var TemporaryFile $tempFile */
                $tempFile = $this->inspectJobProperty($event->job, 'temporaryFile');

                $this->assertInstanceOf(RemoteTemporaryFile::class, $tempFile);

                // Should exist remote
                $this->assertTrue($tempFile->exists());

                $this->assertTrue(unlink($tempFile->getLocalPath()));
            }
        });

        $import = new QueuedImport();

        $chain = $import->queue('import-batches.xlsx')->chain([
            new AfterQueueImportJob(5000),
        ]);

        $this->assertInstanceOf(PendingDispatch::class, $chain);
    }
}
