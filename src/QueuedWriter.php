<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Foundation\Bus\PendingDispatch;
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
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $temporaryFile = $this->temporaryFileFactory->make($extension);

        return new PendingDispatch(
            (new QueueExport($export, $temporaryFile, $writerType))->chain([
                new StoreQueuedExport($temporaryFile, $filePath, $disk, $diskOptions),
            ])
        );
    }
}
