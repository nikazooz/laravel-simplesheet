<?php

namespace Nikazooz\Simplesheet;

use Box\Spout\Common\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Files\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Contracts\Routing\ResponseFactory;
use Nikazooz\Simplesheet\Helpers\FileTypeDetector;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Nikazooz\Simplesheet\Exceptions\NoTypeDetectedException;

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
     * @var \Nikazooz\Simplesheet\Files\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param  \Nikazooz\Simplesheet\Writer  $writer
     * @param  \Nikazooz\Simplesheet\QueuedWriter  $queuedWriter
     * @param  \Nikazooz\Simplesheet\Files\Filesystem  $filesystem
     * @param  \Illuminate\Contracts\Routing\ResponseFactory  $responseFactory
     * @return void
     */
    public function __construct(
        Writer $writer,
        QueuedWriter $queuedWriter,
        Reader $reader,
        Filesystem $filesystem,
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
    public function download($export, string $fileName, string $writerType = null, array $headers = [])
    {
        return $this->responseFactory->download(
           $this->export($export, $fileName, $writerType)->getLocalPath(),
            $fileName,
            $headers
        )->deleteFileAfterSend(true);
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $diskName = null, string $writerType = null, $diskOptions = [])
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $diskName, $writerType, $diskOptions);
        }

        $temporaryFile = $this->export($export, $filePath, $writerType);

        $exported = $this->filesystem->disk($diskName, $diskOptions)->copy(
            $temporaryFile,
            $filePath
        );

        $temporaryFile->delete();

        return $exported;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $diskName = null, string $writerType = null, $diskOptions = [])
    {
        $writerType = FileTypeDetector::detect($filePath, $writerType);

        return $this->queuedWriter->store($export, $filePath, $diskName, $writerType, $diskOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function raw($export, string $writerType)
    {
        $temporaryFile = $this->writer->export($export, $writerType);

        $contents = $temporaryFile->contents();

        $temporaryFile->delete();

        return $contents;
    }

    /**
     * @param  object  $export
     * @param  string  $fileName
     * @param  string|null  $writerType
     * @return string
     *
     * @throws \Nikazooz\Simplesheet\NoTypeDetectedException
     */
    protected function export($export, string $fileName, string $writerType = null, $diskOptions = [])
    {
        $writerType = FileTypeDetector::detect($fileName, $writerType);

        return $this->writer->export($export, $writerType);
    }

    /**
     * {@inheritdoc}
     */
    public function import($import, $filePath, string $disk = null, string $readerType = null)
    {
        $readerType = FileTypeDetector::detect($filePath, $readerType);

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
        $readerType = FileTypeDetector::detect($filePath, $readerType);

        return $this->reader->toArray($import, $filePath, $readerType, $disk);
    }

    /**
     * {@inheritdoc}
     */
    public function toCollection($import, $filePath, string $disk = null, string $readerType = null): Collection
    {
        $readerType = FileTypeDetector::detect($filePath, $readerType);

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
