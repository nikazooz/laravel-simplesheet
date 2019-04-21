<?php

namespace Nikazooz\Simplesheet;

use Throwable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Box\Spout\Reader\ReaderInterface;
use Nikazooz\Simplesheet\Imports\Sheet;
use Nikazooz\Simplesheet\Jobs\QueueImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nikazooz\Simplesheet\Events\AfterImport;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Events\BeforeImport;
use Nikazooz\Simplesheet\Events\ImportFailed;
use Nikazooz\Simplesheet\Files\TemporaryFile;
use Nikazooz\Simplesheet\Factories\ReaderFactory;
use Nikazooz\Simplesheet\Files\TemporaryFileFactory;
use Nikazooz\Simplesheet\Concerns\SkipsUnknownSheets;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Nikazooz\Simplesheet\Events\BeforeTransactionCommit;
use Nikazooz\Simplesheet\Exceptions\SheetNotFoundException;

class Reader
{
    use HasEventBus;

    /**
     * @var TemporaryFileFactory
     */
    protected $temporaryFileFactory;

    /**
     * @param  \Nikazooz\Simplesheet\Files\TemporaryFileFactory  $temporaryFileFactory
     * @return void
     */
    public function __construct(TemporaryFileFactory $temporaryFileFactory)
    {
        $this->temporaryFileFactory = $temporaryFileFactory;
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
        if ($import instanceof ShouldQueue) {
            return QueueImport::dispatch($import, $this->getTemporaryFile($file, $disk), $readerType);
        }

        return $this->readNow($import, $file, $readerType);
    }

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $file
     * @param  string  $readerType
     * @return \Nikazooz\Simplesheet\Reader
     */
    public function readNow($import, $file, string $readerType, string $disk = null)
    {
        $temporaryFile = $this->getTemporaryFile($file, $disk);

        try {
            $reader = $this->getReader($import, $temporaryFile, $readerType);

            $this->beforeReading($import, $reader);

            DB::transaction(function () use ($reader, $import) {
                foreach ($this->sheetImports as $index => $sheetImport) {
                    if ($sheet = $this->getSheet($reader, $import, $sheetImport, $index)) {
                        $sheet->import($sheetImport, $sheet->getStartRow($sheetImport));
                    }
                }

                $this->raise(new BeforeTransactionCommit($this, $import));
            });

            $this->afterReading($import);
            $reader->close();
        } catch (Throwable $e) {
            $this->raise(new ImportFailed($e));
            throw $e;
        }

        $temporaryFile->delete();

        return $this;
    }

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $file
     * @param  string  $readerType
     * @param  string|null  $disk
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Nikazooz\Simplesheet\Exceptions\UnreadableFile
     * @throws \Box\Spout\Reader\Exception\ReaderException
     */
    public function toArray($import, $file, string $readerType, string $disk = null): array
    {
        $temporaryFile = $this->getTemporaryFile($file, $disk);
        $reader = $this->getReader($import, $temporaryFile, $readerType);
        $this->beforeReading($import, $reader);

        $sheets = [];
        foreach ($this->sheetImports as $index => $sheetImport) {
            if ($sheet = $this->getSheet($reader, $import, $sheetImport, $index)) {
                $sheets[$index] = $sheet->toArray($sheetImport);
            }
        }

        $this->afterReading($import);

        $reader->close();
        $temporaryFile->delete();

        return $sheets;
    }

    /**
     * @param  object  $import
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $file
     * @param  string  $readerType
     * @param  string|null  $disk
     * @return \Illuminate\Support\Collection
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Box\Spout\Reader\Exception\ReaderException
     */
    public function toCollection($import, $file, string $readerType, string $disk = null): Collection
    {
        $temporaryFile = $this->getTemporaryFile($file, $disk);
        $reader = $this->getReader($import, $temporaryFile, $readerType);
        $this->beforeReading($import, $reader);

        $sheets = new Collection();
        foreach ($this->sheetImports as $index => $sheetImport) {
            if ($sheet = $this->getSheet($reader, $import, $sheetImport, $index)) {
                $sheets->put($index, $sheet->toCollection($sheetImport));
            }
        }

        $this->afterReading($import);

        $reader->close();
        $temporaryFile->delete();

        return $sheets;
    }

    /**
     * @param  mixed  $file
     * @param  string|null  $disk
     * @return \Nikazooz\Simplesheet\Files\TemporaryFile
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getTemporaryFile($file, string $disk = null): TemporaryFile
    {
        if ($file instanceof TemporaryFile) {
            return $file;
        }

        return $this->temporaryFileFactory->make()->copyFrom($file, $disk);
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

                return;
            }

            if ($sheetImport instanceof SkipsUnknownSheets) {
                $sheetImport->onUnknownSheet($index);

                return;
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
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $readerType
     * @return \Box\Spout\Reader\ReaderInterface
     *
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getReader($import, TemporaryFile $temporaryFile, string $readerType): ReaderInterface
    {
        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        $reader = ReaderFactory::make($readerType, $import);

        $reader->open($temporaryFile->getLocalPath());

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
