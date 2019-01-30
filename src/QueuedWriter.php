<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Jobs\QueueExport;
use Nikazooz\Simplesheet\Concerns\FromQuery;
use Nikazooz\Simplesheet\Jobs\StoreQueuedExport;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Nikazooz\Simplesheet\Concerns\WithCustomChunkSize;

class QueuedWriter
{
    /**
     * @var \Nikazooz\Simplesheet\Writer
     */
    protected $writer;

    /**
     * @param  \Nikazooz\Simplesheet\Writer  $writer
     * @return void
     */
    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param  object  $export
     * @param  string  $filePath
     * @param  string|null  $disk
     * @param  string|null  $writerType
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        $tempFile = $this->writer->tempFile();

        return QueueExport::withChain([
            new StoreQueuedExport($tempFile, $filePath, $disk),
        ])->dispatch($export, $tempFile, $writerType);
    }
}
