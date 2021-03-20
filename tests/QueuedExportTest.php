<?php

namespace Nikazooz\Simplesheet\Tests;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Nikazooz\Simplesheet\Files\RemoteTemporaryFile;
use Nikazooz\Simplesheet\Jobs\QueueExport;
use Nikazooz\Simplesheet\Simplesheet;
use Nikazooz\Simplesheet\Tests\Data\Stubs\EloquentCollectionWithMappingExport;
use Nikazooz\Simplesheet\Tests\Data\Stubs\QueuedExport;
use Nikazooz\Simplesheet\Tests\Data\Stubs\QueuedExportWithFailedHook;
use Nikazooz\Simplesheet\Tests\Data\Stubs\ShouldQueueExport;
use Throwable;

class QueuedExportTest extends TestCase
{
    /**
     * @test
     */
    public function can_queue_an_export()
    {
        $this->expectQueuedExport(function () {
            $export = new QueuedExport();
            $export->queue('queued-export.xlsx');
        }, __DIR__.'/Data/Disks/Local/queued-export.xlsx');
    }

    /**
     * @test
     */
    public function can_queue_an_export_and_store_on_different_disk()
    {
        $this->expectQueuedExport(function () {
            $export = new QueuedExport();
            $export->queue('queued-export.xlsx', 'test');
        }, __DIR__.'/Data/Disks/Test/queued-export.xlsx');
    }

    /**
     * @test
     */
    public function can_queue_export_with_remote_temp_disk()
    {
        config()->set('simplesheet.temporary_files.remote_disk', 'test');

        // Delete the local temp file before each append job
        // to simulate using a shared remote disk, without
        // having a dependency on a local temp file.
        Queue::before(function (JobProcessing $event) use (&$jobs) {
            if ($event->job->resolveName() === QueueExport::class) {
                /** @var TemporaryFile $tempFile */
                $tempFile = $this->inspectJobProperty($event->job, 'temporaryFile');

                $this->assertInstanceOf(RemoteTemporaryFile::class, $tempFile);

                // Should exist remote
                $this->assertTrue(
                    $tempFile->exists()
                );

                $this->assertTrue(
                    unlink($tempFile->getLocalPath())
                );
            }
        });

        $this->expectQueuedExport(function () {
            $export = new QueuedExport();
            $export->queue('queued-export.xlsx');

            $array = $this->readAsArray(__DIR__.'/Data/Disks/Local/queued-export.xlsx', Simplesheet::XLSX);

            $this->assertCount(100, $array);
        }, __DIR__.'/Data/Disks/Local/queued-export.xlsx');
    }

    /**
     * @test
     */
    public function can_implicitly_queue_an_export()
    {
        $this->expectQueuedExport(function () {
            $export = new ShouldQueueExport();
            $export->store('queued-export.xlsx', 'test');
        }, __DIR__.'/Data/Disks/Test/queued-export.xlsx');
    }

    /**
     * @test
     */
    public function can_queue_export_with_mapping_on_eloquent_models()
    {
        $this->expectQueuedExport(function () {
            $export = new EloquentCollectionWithMappingExport();
            $export->queue('queued-export.xlsx');

            $actual = $this->readAsArray(__DIR__.'/Data/Disks/Local/queued-export.xlsx', 'xlsx');

            $this->assertEquals([
                ['John', 'Doe'],
            ], $actual);
        } ,__DIR__.'/Data/Disks/Local/queued-export.xlsx');
    }

    /**
     * @test
     */
    public function can_catch_failures()
    {
        $export = new QueuedExportWithFailedHook();

        try {
            $export->queue('queued-export.xlsx');
        } catch (Throwable $e) {
        }

        $this->assertTrue(app('queue-has-failed'));
    }
}
