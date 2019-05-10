<?php

namespace Nikazooz\Simplesheet\Tests;

use Illuminate\Support\Facades\Queue;
use Nikazooz\Simplesheet\Simplesheet;
use Illuminate\Queue\Events\JobProcessing;
use Nikazooz\Simplesheet\Jobs\QueueExport;
use Nikazooz\Simplesheet\Files\RemoteTemporaryFile;
use Nikazooz\Simplesheet\Tests\Data\Stubs\QueuedExport;
use Nikazooz\Simplesheet\Tests\Data\Stubs\ShouldQueueExport;
use Nikazooz\Simplesheet\Tests\Data\Stubs\AfterQueueExportJob;
use Nikazooz\Simplesheet\Tests\Data\Stubs\EloquentCollectionWithMappingExport;

class QueuedExportTest extends TestCase
{
    /**
     * @test
     */
    public function can_queue_an_export()
    {
        $export = new QueuedExport();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__.'/Data/Disks/Local/queued-export.xlsx'),
        ]);
    }

    /**
     * @test
     */
    public function can_queue_an_export_and_store_on_different_disk()
    {
        $export = new QueuedExport();

        $export->queue('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__.'/Data/Disks/Test/queued-export.xlsx'),
        ]);
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

        $export = new QueuedExport();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__.'/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $array = $this->readAsArray(__DIR__.'/Data/Disks/Local/queued-export.xlsx', Simplesheet::XLSX);

        $this->assertCount(100, $array);
    }

    /**
     * @test
     */
    public function can_implicitly_queue_an_export()
    {
        $export = new ShouldQueueExport();

        $export->store('queued-export.xlsx', 'test')->chain([
            new AfterQueueExportJob(__DIR__.'/Data/Disks/Test/queued-export.xlsx'),
        ]);
    }

    /**
     * @test
     */
    public function can_queue_export_with_mapping_on_eloquent_models()
    {
        $export = new EloquentCollectionWithMappingExport();

        $export->queue('queued-export.xlsx')->chain([
            new AfterQueueExportJob(__DIR__.'/Data/Disks/Local/queued-export.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__.'/Data/Disks/Local/queued-export.xlsx', 'xlsx');

        $this->assertEquals([
            ['John', 'Doe'],
        ], $actual);
    }
}
