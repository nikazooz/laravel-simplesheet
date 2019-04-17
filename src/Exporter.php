<?php

namespace Nikazooz\Simplesheet;

interface Exporter
{
    /**
     * @param  object  $export
     * @param  string  $fileName
     * @param  string|null  $writerType
     * @param  array  $headers
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($export, string $fileName, string $writerType = null, array $headers = []);

    /**
     * @param  object  $export
     * @param  string  $filePath
     * @param  string|null  $diskName
     * @param  string|null  $writerType
     * @param  mixed  $diskOptions
     * @return bool
     */
    public function store($export, string $filePath, string $diskName = null, string $writerType = null,  $diskOptions = []);

    /**
     * @param  object  $export
     * @param  string  $filePath
     * @param  string|null  $diskName
     * @param  string|null  $writerType
     * @param  array  $diskOptions
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function queue($export, string $filePath, string $diskName = null, string $writerType = null,  $diskOptions = []);

    /**
     * @param  object  $export
     * @param  string  $writerType
     * @return string
     */
    public function raw($export, string $writerType);
}
