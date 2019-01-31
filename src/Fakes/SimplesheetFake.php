<?php

namespace Nikazooz\Simplesheet\Fakes;

use Illuminate\Bus\Queueable;
use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Exporter;
use Illuminate\Support\Facades\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SimplesheetFake implements Exporter
{
    /**
     * @var array
     */
    protected $downloads = [];

    /**
     * @var array
     */
    protected $stored = [];

    /**
     * @var array
     */
    protected $queued = [];

    /**
     * @var array
     */
    protected $imported = [];

    /**
     * {@inheritdoc}
     */
    public function download($export, string $fileName, string $writerType = null)
    {
        $this->downloads[$fileName] = $export;

        return new BinaryFileResponse(__DIR__ . '/fake_file');
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $disk, $writerType);
        }

        $this->stored[$disk ?? 'default'][$filePath] = $export;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        Queue::fake();

        $this->stored[$disk ?? 'default'][$filePath] = $export;
        $this->queued[$disk ?? 'default'][$filePath] = $export;

        return new PendingDispatch(new class {
            use Queueable;

            public function handle()
            {
                //
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function import($import, $file, string $disk = null, string $readerType = null)
    {
        if ($import instanceof ShouldQueue) {
            return $this->queueImport($import, $file, $disk, $readerType);
        }

        $filePath = ($file instanceof UploadedFile) ? $file->getClientOriginalName() : $file;

        $this->imported[$disk ?? 'default'][$filePath] = $import;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($import, $file, string $disk = null, string $readerType = null): array
    {
        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->imported[$disk ?? 'default'][$filePath] = $import;

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function toCollection($import, $file, string $disk = null, string $readerType = null): Collection
    {
        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->imported[$disk ?? 'default'][$filePath] = $import;

        return new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function queueImport(ShouldQueue $import, $file, string $disk = null, string $readerType = null)
    {
        Queue::fake();

        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->queued[$disk ?? 'default'][$filePath]   = $import;
        $this->imported[$disk ?? 'default'][$filePath] = $import;

        return new PendingDispatch(new class {
            use Queueable;

            public function handle()
            {
                //
            }
        });
    }

    /**
     * @param  string  $fileName
     * @param  callable|null  $callback
     * @return void
     */
    public function assertDownloaded(string $fileName, $callback = null)
    {
        Assert::assertArrayHasKey($fileName, $this->downloads, sprintf('%s is not downloaded', $fileName));

        $callback = $callback ?: function () {
            return true;
        };

        Assert::assertTrue(
            $callback($this->downloads[$fileName]),
            "The file [{$fileName}] was not downloaded with the expected data."
        );
    }

    /**
     * @param  string  $filePath
     * @param  string|callable|null  $disk
     * @param  callable|null  $callback
     * @return void
     */
    public function assertStored(string $filePath, $disk = null, $callback = null)
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk = null;
        }

        $disk = $disk ?? 'default';
        $storedOnDisk = $this->stored[$disk] ?? [];

        Assert::assertArrayHasKey(
            $filePath,
            $storedOnDisk,
            sprintf('%s is not stored on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: function () {
            return true;
        };

        Assert::assertTrue(
            $callback($storedOnDisk[$filePath]),
            "The file [{$filePath}] was not stored with the expected data."
        );
    }

    /**
     * @param  string  $filePath
     * @param  string|callable|null  $disk
     * @param  callable|null  $callback
     * @return void
     */
    public function assertQueued(string $filePath, $disk = null, $callback = null)
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk = null;
        }

        $disk = $disk ?? 'default';
        $queuedForDisk = $this->queued[$disk] ?? [];

        Assert::assertArrayHasKey(
            $filePath,
            $queuedForDisk,
            sprintf('%s is not queued for export on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: function () {
            return true;
        };

        Assert::assertTrue(
            $callback($queuedForDisk[$filePath]),
            "The file [{$filePath}] was not stored with the expected data."
        );
    }

    /**
     * @param  string  $filePath
     * @param  string|callable|null  $disk
     * @param  callable|null  $callback
     * @return void
     */
    public function assertImported(string $filePath, $disk = null, $callback = null)
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk = null;
        }

        $disk = $disk ?? 'default';
        $importedOnDisk = $this->imported[$disk] ?? [];

        Assert::assertArrayHasKey(
            $filePath,
            $importedOnDisk,
            sprintf('%s is not stored on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: function () {
            return true;
        };

        Assert::assertTrue(
            $callback($importedOnDisk[$filePath]),
            "The file [{$filePath}] was not imported with the expected data."
        );
    }
}