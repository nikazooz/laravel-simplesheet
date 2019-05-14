<?php

namespace Nikazooz\Simplesheet\Concerns;

use Nikazooz\Simplesheet\Exporter;
use Nikazooz\Simplesheet\Exceptions\NoFilenameGivenException;
use Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException;

trait Exportable
{
    /**
     * @param  string|null  $fileName
     * @param  string|null  $writerType
     * @param  array|null  $headers
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilenameGivenException
     */
    public function download(string $fileName = null, string $writerType = null, array $headers = null)
    {
        $headers = $headers ?? $this->headers ?? [];
        $fileName = $fileName ?? $this->fileName ?? null;
        $writerType = $writerType ?? $this->writerType ?? null;

        if (null === $fileName) {
            throw new NoFilenameGivenException();
        }

        return $this->getExporter()->download($this, $fileName, $writerType, $headers);
    }

    /**
     * @param  string|null  $filePath
     * @param  string|null  $disk
     * @param  string|null  $writerType
     * @return bool
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    public function store(string $filePath = null, string $disk = null, string $writerType = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGivenException();
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
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilePathGivenException
     */
    public function queue(string $filePath = null, string $disk = null, string $writerType = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGivenException();
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
     * @throws \Nikazooz\Simplesheet\Exceptions\NoFilenameGivenException
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
