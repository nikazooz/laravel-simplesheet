<?php

namespace Nikazooz\Simplesheet\Jobs;

use Nikazooz\Simplesheet\Reader;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nikazooz\Simplesheet\Facades\Simplesheet;

class QueueImport implements ShouldQueue
{
    use ExtendedQueueable, Dispatchable;

    public $import;

    public $filePath;

    public $readerType;

    public $disk;

    public function __construct($import, $filePath, $readerType, $disk)
    {
        $this->import = $import;
        $this->filePath = $filePath;
        $this->readerType = $readerType;
        $this->disk = $disk;
    }

    public function handle(Reader $reader)
    {
        $reader->readNow($this->import, $this->filePath, $this->readerType, $this->disk);
    }
}
