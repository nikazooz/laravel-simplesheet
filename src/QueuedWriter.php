<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\FromQuery;
use Nikazooz\Simplesheet\Concerns\WithCustomChunkSize;
use Nikazooz\Simplesheet\Concerns\WithCustomQuerySize;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Nikazooz\Simplesheet\Files\TemporaryFile;
use Nikazooz\Simplesheet\Files\TemporaryFileFactory;
use Nikazooz\Simplesheet\Jobs\AppendDataToSheet;
use Nikazooz\Simplesheet\Jobs\AppendQueryToSheet;
use Nikazooz\Simplesheet\Jobs\CloseSheet;
use Nikazooz\Simplesheet\Jobs\QueueExport;
use Nikazooz\Simplesheet\Jobs\StoreQueuedExport;
use Traversable;

class QueuedWriter
{
    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @var \Nikazooz\Simplesheet\Files\TemporaryFileFactory
     */
    protected $temporaryFileFactory;

    /**
     * @param  \Nikazooz\Simplesheet\Files\TemporaryFileFactory  $temporaryFileFactory
     * @return void
     */
    public function __construct(TemporaryFileFactory $temporaryFileFactory)
    {
        $this->chunkSize = config('excel.exports.chunk_size', 1000);
        $this->temporaryFileFactory = $temporaryFileFactory;
    }

    /**
     * @param  object  $export
     * @param  string  $filePath
     * @param  string|null  $disk
     * @param  string|null  $writerType
     * @param  array  $diskOptions
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = [])
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $temporaryFile = $this->temporaryFileFactory->make($extension);

        $jobs = $this->buildExportJobs($export, $temporaryFile, $writerType);

        $jobs->push(new StoreQueuedExport(
            $temporaryFile,
            $filePath,
            $disk,
            $diskOptions
        ));

        return new PendingDispatch(
            (new QueueExport($export, $temporaryFile, $writerType))->chain($jobs->toArray())
        );
    }

    /**
     * @param  object  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     * @return Collection
     */
    private function buildExportJobs($export, TemporaryFile $temporaryFile, string $writerType): Collection
    {
        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        $jobs = new Collection;
        foreach ($sheetExports as $sheetIndex => $sheetExport) {
            if ($sheetExport instanceof FromCollection) {
                $jobs = $jobs->merge($this->exportCollection($sheetExport, $temporaryFile, $writerType, $sheetIndex));
            } elseif ($sheetExport instanceof FromQuery) {
                $jobs = $jobs->merge($this->exportQuery($sheetExport, $temporaryFile, $writerType, $sheetIndex));
            }

            $jobs->push(new CloseSheet($sheetExport, $temporaryFile, $writerType, $sheetIndex));
        }

        return $jobs;
    }

    /**
     * @param  FromCollection  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     * @param  int  $sheetIndex
     * @return Collection
     */
    private function exportCollection(
        FromCollection $export,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex
    ): Collection {
        return $export
            ->collection()
            ->chunk($this->getChunkSize($export))
            ->map(function ($rows) use ($writerType, $temporaryFile, $sheetIndex, $export) {
                if ($rows instanceof Traversable) {
                    $rows = iterator_to_array($rows);
                }

                return new AppendDataToSheet(
                    $export,
                    $temporaryFile,
                    $writerType,
                    $sheetIndex,
                    $rows
                );
            });
    }

    /**
     * @param  FromQuery  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     * @param  int  $sheetIndex
     * @return Collection
     */
    private function exportQuery(
        FromQuery $export,
        TemporaryFile $temporaryFile,
        string $writerType,
        int $sheetIndex
    ): Collection {
        $query = $export->query();

        $count = $export instanceof WithCustomQuerySize ? $export->querySize() : $query->count();
        $spins = ceil($count / $this->getChunkSize($export));

        $jobs = new Collection();

        for ($page = 1; $page <= $spins; $page++) {
            $jobs->push(new AppendQueryToSheet(
                $export,
                $temporaryFile,
                $writerType,
                $sheetIndex,
                $page,
                $this->getChunkSize($export)
            ));
        }

        return $jobs;
    }

    /**
     * @param  object|WithCustomChunkSize  $export
     * @return int
     */
    private function getChunkSize($export): int
    {
        if ($export instanceof WithCustomChunkSize) {
            return $export->chunkSize();
        }

        return $this->chunkSize;
    }
}
