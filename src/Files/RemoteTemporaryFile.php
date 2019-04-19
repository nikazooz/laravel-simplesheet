<?php

namespace Nikazooz\Simplesheet\Files;

class RemoteTemporaryFile extends TemporaryFile
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @var \Nikazooz\Simplesheet\Files\Disk|null
     */
    private $diskInstance;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var \Nikazooz\Simplesheet\Files\LocalTemporaryFile
     */
    private $localTemporaryFile;

    /**
     * @param  string  $disk
     * @param  string  $filename
     * @param  \Nikazooz\Simplesheet\Files\LocalTemporaryFile  $localTemporaryFile
     */
    public function __construct(string $disk, string $filename, LocalTemporaryFile $localTemporaryFile)
    {
        $this->disk = $disk;
        $this->filename = $filename;
        $this->localTemporaryFile = $localTemporaryFile;

        $this->disk()->touch($filename);
    }

    public function __sleep()
    {
        return ['disk', 'filename', 'localTemporaryFile'];
    }

    /**
     * @return string
     */
    public function getLocalPath(): string
    {
        return $this->localTemporaryFile->getLocalPath();
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return $this->disk()->exists($this->filename);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        $this->localTemporaryFile->delete();

        return $this->disk()->delete($this->filename);
    }

    /**
     * @return TemporaryFile
     */
    public function sync(): TemporaryFile
    {
        if (! $this->localTemporaryFile->exists()) {
            touch($this->localTemporaryFile->getLocalPath());
        }

        $this->disk()->copy(
            $this,
            $this->localTemporaryFile->getLocalPath()
        );

        return $this;
    }

    /**
     * Store on remote disk.
     *
     * @return void
     */
    public function updateRemote()
    {
        $this->disk()->copy(
            $this->localTemporaryFile,
            $this->filename
        );
    }

    /**
     * @return resource
     */
    public function readStream()
    {
        return $this->disk()->readStream($this->filename);
    }

    /**
     * @return string
     */
    public function contents(): string
    {
        return $this->disk()->get($this->filename);
    }

    /**
     * @param string|resource $contents
     */
    public function put($contents)
    {
        $this->disk()->put($this->filename, $contents);
    }

    /**
     * @return \Nikazooz\Simplesheet\Files\Disk
     */
    public function disk(): Disk
    {
        return $this->diskInstance ?: $this->diskInstance = app(Filesystem::class)->disk($this->disk);
    }
}
