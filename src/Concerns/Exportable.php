<?php

namespace Nikazooz\Simplesheet\Concerns;

use Nikazooz\Simplesheet\Exporter;
use Nikazooz\Simplesheet\Exceptions\NoFilenameGiven;
use Nikazooz\Simplesheet\Exceptions\NoFilePathGiven;

trait Exportable
{
    /**
     * @param  string  $fileName
     * @param  string|null  $writerType
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilenameGiven
     */
    public function download(string $fileName = null, string $writerType = null)
    {
        $fileName = $fileName ?? $this->fileName ?? null;

        if (null === $fileName) {
            throw new NoFilenameGiven();
        }

        return $this->getExporter()->download($this, $fileName, $writerType ?? $this->writerType ?? null);
    }

    /**
     * @param  string  $filePath
     * @param  string|null  $disk
     * @param  string|null  $writerType
     * @return bool
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGiven
     */
    public function store(string $filePath = null, string $disk = null, string $writerType = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGiven();
        }

        return $this->getExporter()->store(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null
        );
    }

    /**
     * @param  string|null  $filePath
     * @param  string|null  $disk
     * @param  string|null  $writerType
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGiven
     */
    public function queue(string $filePath = null, string $disk = null, string $writerType = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGiven();
        }

        return $this->getExporter()->queue(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $writerType ?? $this->writerType ?? null
        );
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilenameGiven
     */
    public function toResponse($request)
    {
        return $this->download();
    }

    /**
     * @return \Nikazooz\Simplesheet\Exporter
     */
    private function getExporter(): Exporter
    {
        return app(Exporter::class);
    }
}
