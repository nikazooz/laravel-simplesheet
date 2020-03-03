<?php

namespace Nikazooz\Simplesheet;

use Nikazooz\Simplesheet\Files\TemporaryFileFactory;
use Nikazooz\Simplesheet\Jobs\QueueExport;
use Nikazooz\Simplesheet\Jobs\StoreQueuedExport;

class QueuedWriter
{
    /**
     * @var \Nikazooz\Simplesheet\Files\TemporaryFileFactory
     */
    protected $temporaryFileFactory;

    /**
     * @param  \Nikazooz\Simplesheet\Files\TemporaryFileFactory  $temporaryFileFactory
     * @return void
     */
    public function __construct(TemporaryFileFactory $temporaryFileFactory)
    {
        $this->temporaryFileFactory = $temporaryFileFactory;
    }

    /**
     * @param  object  $export
     * @param  string  $filePath
     * @param  string|null  $disk
     * @param  string|null  $writerType
     * @param  array  $diskOptions
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = [])
    {
        $temporaryFile = $this->temporaryFileFactory->make();

        return QueueExport::withChain([
            new StoreQueuedExport($temporaryFile, $filePath, $disk, $diskOptions),
        ])->dispatch($export, $temporaryFile, $writerType);
    }
}
