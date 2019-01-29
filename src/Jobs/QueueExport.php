<?php

namespace Nikazooz\Simplesheet\Jobs;

use Nikazooz\Simplesheet\Writer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;

class QueueExport implements ShouldQueue
{
    use ExtendedQueueable, Dispatchable;

    /**
     * @var object
     */
    private $export;

    /**
     * @var string
     */
    private $tempFile;

    /**
     * @var string
     */
    private $writerType;

    /**
     * @param  object  $export
     * @param  string  $tempFile
     * @param  string  $writerType
     * @return void
     */
    public function __construct($export, string $tempFile, string $writerType)
    {
        $this->export = $export;
        $this->tempFile = $tempFile;
        $this->writerType = $writerType;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Writer $writer
     * @return void
     */
    public function handle(Writer $writer)
    {
        $writer->export($this->export, $this->writerType, $this->tempFile);
    }
}
