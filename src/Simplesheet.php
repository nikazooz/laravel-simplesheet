<?php

namespace Nikazooz\Simplesheet;

use Box\Spout\Common\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Routing\ResponseFactory;
use Nikazooz\Simplesheet\Exceptions\NoTypeDetected;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class Simplesheet implements Exporter
{
    const CSV = Type::CSV;
    const ODS = Type::ODS;
    const XLSX = Type::XLSX;

    /**
     * @var \Nikazooz\Simplesheet\Writer
     */
    protected $writer;

    /**
     * @var \Nikazooz\Simplesheet\QueuedWriter
     */
    protected $queuedWriter;

    /**
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystem;

    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @param  \Nikazooz\Simplesheet\Writer  $writer
     * @param  \Nikazooz\Simplesheet\QueuedWriter  $queuedWriter
     * @param  \Illuminate\Contracts\Filesystem\Factory  $filesystem
     * @param  \Illuminate\Contracts\Routing\ResponseFactory  $responseFactory
     */
    public function __construct(
        Writer $writer,
        QueuedWriter $queuedWriter,
        FilesystemFactory $filesystem,
        ResponseFactory $responseFactory
    ) {
        $this->writer = $writer;
        $this->queuedWriter = $queuedWriter;
        $this->filesystem = $filesystem;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function download($export, string $fileName, string $writerType = null)
    {
        $file = $this->export($export, $fileName, $writerType);

        return $this->responseFactory->download($file, $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $disk, $writerType);
        }

        $file = $this->export($export, $filePath, $writerType);

        return $this->filesystem->disk($disk)->put($filePath, fopen($file, 'r+'));
    }

    /**
     * @param  object  $export
     * @param  string  $fileName
     * @param  string|null  $writerType
     * @return string
     *
     * @throws \Nikazooz\Simplesheet\NoTypeDetected
     */
    protected function export($export, string $fileName, string $writerType = null)
    {
        $writerType = $this->findTypeByExtension($fileName, $writerType);

        return $this->writer->export($export, $writerType);
    }

     /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        $writerType = $this->findTypeByExtension($filePath, $writerType);

        return $this->queuedWriter->store($export, $filePath, $disk, $writerType);
    }

    /**
     * @param  string  $fileName
     * @param  string|null  $type
     * @return string
     *
     * @throws \Nikazooz\Simplesheet\NoTypeDetected
     */
    protected function findTypeByExtension($fileName, string $type = null)
    {
        if (null !== $type) {
            return $type;
        }

        $pathInfo  = pathinfo($fileName);
        $extension = strtolower(trim($pathInfo['extension'] ?? ''));

        if ($extension === '' || ! array_key_exists($extension, $this->extensions)) {
            throw new NoTypeDetected();
        }

        return $this->extensions[$extension];
    }

    /**
     * @param  array  $extensions
     * @return $this
     */
    public function setExtensionsMap(array $extensions = [])
    {
        $this->extensions = $extensions;

        return $this;
    }
}
