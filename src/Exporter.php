<?php

namespace Nikazooz\Simplesheet;

interface Exporter
{
    /**
     * @param  object  $export
     * @param  string|null  $fileName
     * @param  string  $writerType
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($export, string $fileName, string $writerType = null);

    /**
     * @param  object  $export
     * @param  string  $filePath
     * @param  string|null  $disk
     * @param  string  $writerType
     * @return bool
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null);

    /**
     * @param  object  $export
     * @param  string  $filePath
     * @param  string|null  $disk
     * @param  string  $writerType
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null);
}
