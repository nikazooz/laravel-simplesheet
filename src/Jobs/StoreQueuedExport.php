<?php

namespace Nikazooz\Simplesheet\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nikazooz\Simplesheet\Files\Filesystem;
use Nikazooz\Simplesheet\Files\TemporaryFile;

class StoreQueuedExport implements ShouldQueue
{
    use Queueable;

    /**
     * @var TemporaryFile
     */
    private $temporaryFile;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $disk;

    /**
     * @var array
     */
    private $diskOptions = [];

    /**
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $path
     * @param  string|null  $disk
     * @param  array  $diskOptions
     * @return void
     */
    public function __construct(TemporaryFile $temporaryFile, string $path, string $disk = null, array $diskOptions = [])
    {
        $this->path = $path;
        $this->disk = $disk;
        $this->diskOptions = $diskOptions;
        $this->temporaryFile = $temporaryFile;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Files\Filesystem  $filesystem
     * @return void
     */
    public function handle(Filesystem $filesystem)
    {
        $filesystem->disk($this->disk, $this->diskOptions)->copy(
            $this->temporaryFile,
            $this->path
        );

        $this->temporaryFile->delete();
    }
}
