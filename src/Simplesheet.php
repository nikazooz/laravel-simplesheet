<?php

namespace Nikazooz\Simplesheet;

use Box\Spout\Common\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Nikazooz\Simplesheet\Exceptions\NoTypeDetectedException;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class Simplesheet implements Exporter, Importer
{
    const CSV = Type::CSV;
    const TSV = 'tsv';
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
     * @var \Nikazooz\Simplesheet\Reader
     */
    protected $reader;

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
        Reader $reader,
        FilesystemFactory $filesystem,
        ResponseFactory $responseFactory
    ) {
        $this->writer = $writer;
        $this->queuedWriter = $queuedWriter;
        $this->reader = $reader;
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
     * @throws \Nikazooz\Simplesheet\NoTypeDetectedException
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
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $fileName
     * @param  string|null  $type
     * @return string
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\NoTypeDetectedException
     */
    public function findTypeByExtension($fileName, string $type = null)
    {
        if (null !== $type) {
            return $type;
        }

        $extension = strtolower(trim($this->getRawExtension($fileName)));

        if ($extension === '' || ! array_key_exists($extension, $this->extensions)) {
            throw new NoTypeDetectedException();
        }

        return $this->extensions[$extension];
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $fileName
     * @return string
     */
    protected function getRawExtension($fileName)
    {
        if ($fileName instanceof UploadedFile) {
            return $fileName->getClientOriginalExtension();
        }

        $pathInfo  = pathinfo($fileName);

        return $pathInfo['extension'] ?? '';
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

    /**
     * {@inheritdoc}
     */
    public function import($import, $filePath, string $disk = null, string $readerType = null)
    {
        $readerType = $this->findTypeByExtension($filePath, $readerType);

        $response = $this->reader->read($import, $filePath, $readerType, $disk);

        if ($response instanceof PendingDispatch) {
            return $response;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($import, $filePath, string $disk = null, string $readerType = null): array
    {
        $readerType = $this->findTypeByExtension($filePath, $readerType);

        return $this->reader->toArray($import, $filePath, $readerType, $disk);
    }

    /**
     * {@inheritdoc}
     */
    public function toCollection($import, $filePath, string $disk = null, string $readerType = null): Collection
    {
        $readerType = $this->findTypeByExtension($filePath, $readerType);

        return $this->reader->toCollection($import, $filePath, $readerType, $disk);
    }

    /**
     * {@inheritdoc}
     */
    public function queueImport(ShouldQueue $import, $filePath, string $disk = null, string $readerType = null)
    {
        return $this->import($import, $filePath, $disk, $readerType);
    }
}
