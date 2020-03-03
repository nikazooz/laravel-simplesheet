<?php

namespace Nikazooz\Simplesheet\Concerns;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException;
use Nikazooz\Simplesheet\Importer;

trait Importable
{
    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string|null  $filePath
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return \Nikazooz\Simplesheet\Importer|\Illuminate\Foundation\Bus\PendingDispatch
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    public function import($filePath = null, string $disk = null, string $readerType = null)
    {
        return $this->getImporter()->import(
            $this,
            $this->getFilePath($filePath),
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
        return $this->getImporter()->toArray(
            $this,
            $this->getFilePath($filePath),
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
        return $this->getImporter()->toCollection(
            $this,
            $this->getFilePath($filePath),
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
     * @throws \InvalidArgumentException
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    public function queue($filePath = null, string $disk = null, string $readerType = null)
    {
        if (! $this instanceof ShouldQueue) {
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
            throw NoFilePathGivenException::import();
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
