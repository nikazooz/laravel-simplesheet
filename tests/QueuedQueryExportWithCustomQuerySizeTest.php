<?php

namespace Nikazooz\Simplesheet\Tests;

use Nikazooz\Simplesheet\Tests\Data\Stubs\AfterQueueExportJob;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use Nikazooz\Simplesheet\Tests\Data\Stubs\FromUsersQueryExportWithMappingAndCustomQuerySize;
use Nikazooz\Simplesheet\Tests\Data\Stubs\FromUsersQueryWithCustomQuerySizeExport;

class QueuedQueryExportWithCustomQuerySizeTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__.'/Data/Stubs/Database/Factories');

        factory(User::class)->times(100)->create([]);
    }

    /**
     * @test
     */
    public function can_queue_an_export_with_custom_query_size()
    {
        $export = new FromUsersQueryWithCustomQuerySizeExport();

        $export->queue('queued-query-export-with-custom-query-size.xlsx')->chain([
            new AfterQueueExportJob(__DIR__.'/Data/Disks/Local/queued-query-export-with-custom-query-size.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__.'/Data/Disks/Local/queued-query-export-with-custom-query-size.xlsx', 'xlsx');

        $this->assertCount(100, $actual);

        // 6 of the 7 columns in export, excluding the "hidden" password column.
        $this->assertCount(6, $actual[0]);
    }

    /**
     * @test
     */
    public function can_queue_an_export_with_mapping_and_custom_query_size()
    {
        $export = new FromUsersQueryExportWithMappingAndCustomQuerySize();

        $export->queue('queued-query-export-with-mapping-and-custom-query-size.xlsx')->chain([
            new AfterQueueExportJob(__DIR__.'/Data/Disks/Local/queued-query-export-with-mapping-and-custom-query-size.xlsx'),
        ]);

        $actual = $this->readAsArray(__DIR__.'/Data/Disks/Local/queued-query-export-with-mapping-and-custom-query-size.xlsx', 'xlsx');

        $this->assertCount(100, $actual);

        // Only 1 column when using map()
        $this->assertCount(1, $actual[0]);
        $this->assertEquals(User::value('name'), $actual[0][0]);
    }
}
