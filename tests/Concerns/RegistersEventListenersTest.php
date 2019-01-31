<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Sheet;
use Nikazooz\Simplesheet\Reader;
use Nikazooz\Simplesheet\Writer;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Events\AfterSheet;
use Nikazooz\Simplesheet\Events\AfterImport;
use Nikazooz\Simplesheet\Events\BeforeSheet;
use Nikazooz\Simplesheet\Events\BeforeExport;
use Nikazooz\Simplesheet\Events\BeforeImport;
use Nikazooz\Simplesheet\Events\BeforeWriting;
use Nikazooz\Simplesheet\Imports\Sheet as ImportSheet;
use Nikazooz\Simplesheet\Events\BeforeTransactionCommit;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Nikazooz\Simplesheet\Tests\Data\Stubs\ExportWithEvents;
use Nikazooz\Simplesheet\Tests\Data\Stubs\BeforeExportListener;
use Nikazooz\Simplesheet\Tests\Data\Stubs\ExportWithRegistersEventListeners;
use Nikazooz\Simplesheet\Tests\Data\Stubs\ImportWithRegistersEventListeners;

class RegistersEventListenersTest extends TestCase
{
    /**
     * @test
     */
    public function events_get_called_when_exporting()
    {
        $event = new ExportWithRegistersEventListeners();

        $eventsTriggered = 0;

        $event::$beforeExport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
            $eventsTriggered++;
        };

        $event::$beforeWriting = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeWriting::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
            $eventsTriggered++;
        };

        $event::$beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event::$afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
        $this->assertEquals(4, $eventsTriggered);
    }

    /**
     * @test
     */
    public function events_get_called_when_importing()
    {
        $event = new ImportWithRegistersEventListeners();

        $eventsTriggered = 0;

        $event::$beforeImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->reader);
            $eventsTriggered++;
        };

        $event::$afterImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->reader);
            $eventsTriggered++;
        };

        $event::$beforeTransactionCommit = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeTransactionCommit::class, $event);
            $this->assertInstanceOf(Reader::class, $event->reader);
            $eventsTriggered++;
        };

        $event::$beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(ImportSheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event::$afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(ImportSheet::class, $event->sheet);
            $eventsTriggered++;
        };

        $event->import('import.xlsx');
        $this->assertEquals(5, $eventsTriggered);
    }

    /**
     * @test
     */
    public function can_have_invokable_class_as_listener()
    {
        $event = new ExportWithEvents();

        $event->beforeExport = new BeforeExportListener(function ($event) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->writer);
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }
}
