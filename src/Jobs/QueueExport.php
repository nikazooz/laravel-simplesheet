<?php

namespace Nikazooz\Simplesheet\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nikazooz\Simplesheet\Files\TemporaryFile;
use Nikazooz\Simplesheet\Writer;
use Throwable;

class QueueExport implements ShouldQueue
{
    use ExtendedQueueable, Dispatchable;

    /**
     * @var object
     */
    private $export;

    /**
     * @var TemporaryFile
     */
    private $temporaryFile;

    /**
     * @var string
     */
    private $writerType;

    /**
     * @param  object  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     * @return void
     */
    public function __construct($export, TemporaryFile $temporaryFile, string $writerType)
    {
        $this->export = $export;
        $this->writerType = $writerType;
        $this->temporaryFile = $temporaryFile;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Writer  $writer
     * @return void
     */
    public function handle(Writer $writer)
    {
        $writer->export($this->export, $this->writerType, $this->temporaryFile->sync());
    }

    /**
     * @param  \Throwable  $e
     * @return  void
     */
    public function failed(Throwable $e)
    {
        if (method_exists($this->export, 'failed')) {
            $this->export->failed($e);
        }
    }
}
