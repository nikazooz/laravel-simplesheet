<?php

namespace Nikazooz\Simplesheet\Files;

use Illuminate\Contracts\Filesystem\Factory;

class Filesystem
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    private $filesystem;

    /**
     * @param  \Illuminate\Contracts\Filesystem\Factory  $filesystem
     * @return void
     */
    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param  string|null  $disk
     * @param  array  $diskOptions
     * @return \Nikazooz\Simplesheet\Files\Disk
     */
    public function disk(string $disk = null, array $diskOptions = []): Disk
    {
        return new Disk(
            $this->filesystem->disk($disk),
            $disk,
            $diskOptions
        );
    }
}
