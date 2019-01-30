<?php

namespace Nikazooz\Simplesheet\Concerns;

use InvalidArgumentException;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Importer;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait Importable
{
    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string|null  $filePath
     * @param  string|null  $disk
     * @param  string|null  $readerType
     *
     * @throws NoFilePathGivenExceptionException
     * @return Importer|PendingDispatch
     */
    public function import($filePath = null, string $disk = null, string $readerType = null)
    {
        $filePath = $this->getFilePath($filePath);

        return $this->getImporter()->import(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null
        );
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string|null  $filePath
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return array
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    public function toArray($filePath = null, string $disk = null, string $readerType = null): array
    {
        $filePath = $this->getFilePath($filePath);

        return $this->getImporter()->toArray(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null
        );
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string|null  $filePath
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return \Illuminate\Support\Collection
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    public function toCollection($filePath = null, string $disk = null, string $readerType = null): Collection
    {
        $filePath = $this->getFilePath($filePath);

        return $this->getImporter()->toCollection(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null
        );
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string|null  $filePath
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     * @throws \InvalidArgumentException
     */
    public function queue($filePath = null, string $disk = null, string $readerType = null)
    {
        if (!$this instanceof ShouldQueue) {
            throw new InvalidArgumentException('Importable should implement ShouldQueue to be queued.');
        }

        return $this->import($filePath, $disk, $readerType);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string|null  $filePath
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|string
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    private function getFilePath($filePath = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGivenException('A filepath needs to be passed in order to perform the import.');
        }

        return $filePath;
    }

    /**
     * @return \Nikazooz\Simplesheet\Importer
     */
    private function getImporter(): Importer
    {
        return app(Importer::class);
    }
}
