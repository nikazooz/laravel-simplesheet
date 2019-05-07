<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;

interface Importer
{
    /**
     * @param  object  $import
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return \Illuminate\Foundation\Bus\PendingDispatch|Reader
     */
    public function import($import, $filePath, string $disk = null, string $readerType = null);

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $filePath
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return array
     */
    public function toArray($import, $filePath, string $disk = null, string $readerType = null): array;

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $filePath
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return \Illuminate\Support\Collection
     */
    public function toCollection($import, $filePath, string $disk = null, string $readerType = null): Collection;

    /**
     * @param  \Illuminate\Contracts\Queue\ShouldQueue  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $filePath
     * @param  string|null  $disk
     * @param  string  $readerType
     * @return Illuminate\Foundation\Bus\PendingDispatch
     */
    public function queueImport(ShouldQueue $import, $filePath, string $disk = null, string $readerType = null);
}
