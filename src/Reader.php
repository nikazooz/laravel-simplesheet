<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Box\Spout\Reader\ReaderInterface;
use Nikazooz\Simplesheet\HasEventBus;
use Nikazooz\Simplesheet\Simplesheet;
use Nikazooz\Simplesheet\Imports\Sheet;
use Nikazooz\Simplesheet\Jobs\QueueImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nikazooz\Simplesheet\Events\AfterImport;
use Box\Spout\Reader\CSV\Reader as CsvReader;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Events\BeforeImport;
use Nikazooz\Simplesheet\Factories\ReaderFactory;
use Nikazooz\Simplesheet\Concerns\MapsCsvSettings;
use Nikazooz\Simplesheet\Concerns\SkipsUnknownSheets;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nikazooz\Simplesheet\Exceptions\SheetNotFoundException;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class Reader
{
    use HasEventBus, MapsCsvSettings;

    /**
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    private $filesystem;

     /**
     * @var string
     */
    protected $tempPath;

    /**
     * @param  \Illuminate\Contracts\Filesystem\Factory  $filesystem
     * @param  string  $tempPath
     * @param  array  $csvSettings
     * @return void
     */
    public function __construct(FilesystemFactory $filesystem, string $tempPath, array $csvSettings = [])
    {
        $this->filesystem = $filesystem;
        $this->tempPath = $tempPath;

        $this->applyCsvSettings($csvSettings);
    }

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $file
     * @param  string  $readerType
     * @param  string|null  $disk
     * @return \Illuminate\Foundation\Bus\PendingDispatch|\Nikazooz\Simplesheet\Reader
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function read($import, $file, string $readerType, string $disk = null)
    {
        $filePath = $this->copyToFileSystem($file, $disk);

        if ($import instanceof ShouldQueue) {
            return QueueImport::dispatch($import, $filePath, $readerType);
        }

        return $this->readNow($import, $filePath, $readerType);
    }

    /**
     * @param  object  $import
     * @param  string  $filePath
     * @param  string  $readerType
     * @return \Nikazooz\Simplesheet\Reader
     */
    public function readNow($import, $filePath, string $readerType)
    {
        $reader = $this->getReader($import, $filePath, $readerType);

        $this->beforeReading($import, $reader);

        DB::transaction(function () use ($reader, $import) {
            foreach ($this->sheetImports as $index => $sheetImport) {
                if ($sheet = $this->getSheet($reader, $import, $sheetImport, $index)) {
                    $sheet->import($sheetImport, $sheet->getStartRow($sheetImport));
                }
            }

            $this->afterReading($import);
        });

        $reader->close();

        return $this;
    }

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $file
     * @param  string  $readerType
     * @param  string|null  $disk
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Nikazooz\Simplesheet\Exceptions\UnreadableFile
     * @throws \Box\Spout\Reader\Exception\ReaderException
     * @return array
     */
    public function toArray($import, $file, string $readerType, string $disk = null): array
    {
        $reader = $this->getReader($import, $this->copyToFileSystem($file, $disk), $readerType);
        $this->beforeReading($import, $reader);

        $sheets = [];
        foreach ($this->sheetImports as $index => $sheetImport) {
            if ($sheet = $this->getSheet($reader, $import, $sheetImport, $index)) {
                $sheets[$index] = $sheet->toArray($sheetImport);
            }
        }

        $this->afterReading($import);

        $reader->close();

        return $sheets;
    }

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $file
     * @param  string  $readerType
     * @param  string|null  $disk
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Box\Spout\Reader\Exception\ReaderException
     * @return \Illuminate\Support\Collection
     */
    public function toCollection($import, $file, string $readerType, string $disk = null): Collection
    {
        $reader = $this->getReader($import, $this->copyToFileSystem($file, $disk), $readerType);
        $this->beforeReading($import, $reader);

        $sheets = new Collection();
        foreach ($this->sheetImports as $index => $sheetImport) {
            if ($sheet = $this->getSheet($reader, $import, $sheetImport, $index)) {
                $sheets->put($index, $sheet->toCollection($sheetImport));
            }
        }

        $this->afterReading($import);

        $reader->close();

        return $sheets;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $file
     * @param  string|null  $disk
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function copyToFileSystem($file, string $disk = null)
    {
        $tempFilePath = $this->getTempFile();

        if ($file instanceof UploadedFile) {
            return $file->move($tempFilePath)->getRealPath();
        }

        $tmpStream = fopen($tempFilePath, 'w+');

        $readStream = $this->filesystem->disk($disk)->readStream($file);

        stream_copy_to_stream($readStream, $tmpStream);
        fclose($tmpStream);

        return $tempFilePath;
    }

    /**
     * @return string
     */
    protected function getTempFile(): string
    {
        return $this->tempPath . DIRECTORY_SEPARATOR . str_random(16);
    }

    /**
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @param  object  $import
     * @param  object  $sheetImport
     * @param  string|int  $index
     * @return \Nikazooz\Simplesheet\Imports\Sheet|null
     *
     * @throws \Nikazooz\Simplesheet\Exceptions\SheetNotFoundException
     */
    protected function getSheet(ReaderInterface $reader, $import, $sheetImport, $index)
    {
        try {
            return Sheet::make($reader, $index);
        } catch (SheetNotFoundException $e) {
            if ($import instanceof SkipsUnknownSheets) {
                $import->onUnknownSheet($index);

                return null;
            }

            if ($sheetImport instanceof SkipsUnknownSheets) {
                $sheetImport->onUnknownSheet($index);

                return null;
            }

            throw $e;
        }
    }

    /**
     * Garbage collect.
     */
    private function garbageCollect()
    {
        // Force garbage collecting
        unset($this->sheetImports);
    }

    /**
     * @param  object  $import
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @return array
     */
    private function buildSheetImports($import, ReaderInterface $reader): array
    {
        if ($import instanceof WithMultipleSheets) {
            return $import->sheets();
        }

        // When there are no multiple sheets, use the main import object
        // for each loaded sheet in the spreadsheet
        return array_fill(0, $this->getSheetCount($reader), $import);
    }

    /**
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @return int
     */
    private function getSheetCount(ReaderInterface $reader): int
    {
        return count(iterator_to_array($reader->getSheetIterator()));
    }

    /**
     * @param  object  $import
     * @param  string  $filePath
     * @param  string  $readerType
     * @return \Box\Spout\Reader\ReaderInterface
     *
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getReader($import, $filePath, string $readerType): ReaderInterface
    {
        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        if (Simplesheet::TSV === $readerType) {
            $this->delimiter = "\t";
        }

        if ($import instanceof WithCustomCsvSettings) {
            $this->applyCsvSettings($import->getCsvSettings());
        }

        $reader = ReaderFactory::create($readerType);

        if ($reader instanceof CsvReader) {
            $reader->setFieldDelimiter($this->delimiter);
            $reader->setFieldEnclosure($this->enclosure);
            $reader->setEncoding($this->inputEncoding);
        }

        $reader->open($filePath);

        return $reader;
    }

    /**
     * @param  object  $import
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @return void
     */
    private function beforeReading($import, ReaderInterface $reader)
    {
        $this->sheetImports = $this->buildSheetImports($import, $reader);

        $this->raise(new BeforeImport($this, $import));
    }

    /**
     * @param  object  $import
     * @return void
     */
    private function afterReading($import)
    {
        $this->raise(new AfterImport($this, $import));
        $this->garbageCollect();
    }
}
