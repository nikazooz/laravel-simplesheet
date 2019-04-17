<?php

namespace Nikazooz\Simplesheet\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemManager;

class StoreQueuedExport implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    private $tmpPath;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $disk;

    /**
     * @param  string  $tmpPath
     * @param  string  $path
     * @param  string|null  $disk
     * @return void
     */
    public function __construct(string $tmpPath, string $path, string $disk = null)
    {
        $this->tmpPath = $tmpPath;
        $this->path = $path;
        $this->disk = $disk;
    }

    /**
     * @param  \Illuminate\Filesystem\FilesystemManager  $filesystem
     * @return void
     */
    public function handle(FilesystemManager $filesystem)
    {
        $filesystem->disk($this->disk)->put($this->path, fopen($this->tmpPath, 'r+'));
    }
}
