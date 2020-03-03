<?php

namespace Nikazooz\Simplesheet\Fakes;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Nikazooz\Simplesheet\Exporter;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @var bool
     */
    protected $matchByRegex = false;

    /**
     * {@inheritdoc}
     */
    public function download($export, string $fileName, string $writerType = null, array $headers = [])
    {
        $this->downloads[$fileName] = $export;

        return new BinaryFileResponse(__DIR__.'/fake_file');
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $diskName = null, string $writerType = null, $diskOptions = [])
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $diskName, $writerType);
        }

        $this->stored[$diskName ?? 'default'][$filePath] = $export;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $diskName = null, string $writerType = null, $diskOptions = [])
    {
        Queue::fake();

        $this->stored[$diskName ?? 'default'][$filePath] = $export;
        $this->queued[$diskName ?? 'default'][$filePath] = $export;

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
    public function raw($export, string $writerType)
    {
        return 'RAW-CONTENTS';
    }

    /**
     * {@inheritdoc}
     */
    public function import($import, $file, string $diskName = null, string $readerType = null)
    {
        if ($import instanceof ShouldQueue) {
            return $this->queueImport($import, $file, $diskName, $readerType);
        }

        $filePath = ($file instanceof UploadedFile) ? $file->getClientOriginalName() : $file;

        $this->imported[$diskName ?? 'default'][$filePath] = $import;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($import, $file, string $diskName = null, string $readerType = null): array
    {
        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->imported[$diskName ?? 'default'][$filePath] = $import;

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function toCollection($import, $file, string $diskName = null, string $readerType = null): Collection
    {
        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->imported[$diskName ?? 'default'][$filePath] = $import;

        return new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function queueImport(ShouldQueue $import, $file, string $diskName = null, string $readerType = null)
    {
        Queue::fake();

        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->queued[$diskName ?? 'default'][$filePath] = $import;
        $this->imported[$diskName ?? 'default'][$filePath] = $import;

        return new PendingDispatch(new class {
            use Queueable;

            public function handle()
            {
                //
            }
        });
    }

    /**
     * When asserting downloaded, stored, queued or imported, use regular expression
     * to look for a matching file path.
     *
     * @return void
     */
    public function matchByRegex()
    {
        $this->matchByRegex = true;
    }

    /**
     * When asserting downloaded, stored, queued or imported, use regular string
     * comparison for matching file path.
     *
     * @return void
     */
    public function doNotMatchByRegex()
    {
        $this->matchByRegex = false;
    }

    /**
     * @param  string  $fileName
     * @param  callable|null  $callback
     * @return void
     */
    public function assertDownloaded(string $fileName, $callback = null)
    {
        $fileName = $this->assertArrayHasKey($fileName, $this->downloads, sprintf('%s is not downloaded', $fileName));

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
     * @param  string|callable|null  $diskName
     * @param  callable|null  $callback
     * @return void
     */
    public function assertStored(string $filePath, $diskName = null, $callback = null)
    {
        if (is_callable($diskName)) {
            $callback = $diskName;
            $diskName = null;
        }

        $diskName = $diskName ?? 'default';
        $storedOnDisk = $this->stored[$diskName] ?? [];

        $filePath = $this->assertArrayHasKey(
            $filePath,
            $storedOnDisk,
            sprintf('%s is not stored on disk %s', $filePath, $diskName)
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

        $filePath = $this->assertArrayHasKey(
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

        $filePath = $this->assertArrayHasKey(
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

    /**
     * Asserts that an array has a specified key and returns the key if successful.
     *
     * @see matchByRegex for more information about file path matching
     *
     * @param  string  $key
     * @param  array  $array
     * @param  string  $message
     * @return string
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     */
    protected function assertArrayHasKey(string $key, array $disk, string $message = ''): string
    {
        if ($this->matchByRegex) {
            $files = array_keys($disk);
            $results = preg_grep($key, $files);

            Assert::assertGreaterThan(0, count($results), $message);
            Assert::assertEquals(1, count($results), "More than one result matches the file name expression '$key'.");

            return $results[0];
        }

        Assert::assertArrayHasKey($key, $disk, $message);

        return $key;
    }
}
