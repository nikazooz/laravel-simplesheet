<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Sheet;
use Nikazooz\Simplesheet\Reader;
use Nikazooz\Simplesheet\Writer;
use Nikazooz\Simplesheet\Simplesheet;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Events\AfterSheet;
use Nikazooz\Simplesheet\Events\AfterImport;
use Nikazooz\Simplesheet\Events\BeforeSheet;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Events\BeforeExport;
use Nikazooz\Simplesheet\Events\BeforeImport;
use Nikazooz\Simplesheet\Events\BeforeWriting;
use Nikazooz\Simplesheet\Imports\Sheet as ImportSheet;
use Nikazooz\Simplesheet\Tests\Data\Stubs\CustomConcern;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Nikazooz\Simplesheet\Tests\Data\Stubs\ExportWithEvents;
use Nikazooz\Simplesheet\Tests\Data\Stubs\ImportWithEvents;
use Nikazooz\Simplesheet\Tests\Data\Stubs\CustomSheetConcern;
use Nikazooz\Simplesheet\Tests\Data\Stubs\BeforeExportListener;

class WithEventsTest extends TestCase
{
    /**
     * @test
     */
    public function export_events_get_called()
    {
        $event = new ExportWithEvents();

        $eventsTriggered = 0;

        $event->beforeExport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeWriting = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeWriting::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $event->afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
        $this->assertEquals(4, $eventsTriggered);
    }

    /**
     * @test
     */
    public function import_events_get_called()
    {
        $event = new ImportWithEvents();

        $eventsTriggered = 0;

        $event->beforeImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $eventsTriggered++;
        };

        $event->afterImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $eventsTriggered++;
        };

        $event->beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(ImportSheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $event->afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(ImportSheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $event->import('import.xlsx');
        $this->assertEquals(4, $eventsTriggered);
    }

    /**
     * @test
     */
    public function can_have_invokable_class_as_listener()
    {
        $event = new ExportWithEvents();

        $event->beforeExport = new BeforeExportListener(function ($event) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }

    /**
     * @test
     */
    public function can_have_global_event_listeners()
    {
        $event = new class {
            use Exportable;
        };

        $beforeExport = false;
        Writer::listen(BeforeExport::class, function () use (&$beforeExport) {
            $beforeExport = true;
        });

        $beforeWriting = false;
        Writer::listen(BeforeWriting::class, function () use (&$beforeWriting) {
            $beforeWriting = true;
        });

        $beforeSheet = false;
        Sheet::listen(BeforeSheet::class, function () use (&$beforeSheet) {
            $beforeSheet = true;
        });

        $afterSheet = false;
        Sheet::listen(AfterSheet::class, function () use (&$afterSheet) {
            $afterSheet = true;
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));

        $this->assertTrue($beforeExport, 'Before export event not triggered');
        $this->assertTrue($beforeWriting, 'Before writing event not triggered');
        $this->assertTrue($beforeSheet, 'Before sheet event not triggered');
        $this->assertTrue($afterSheet, 'After sheet event not triggered');
    }
}
