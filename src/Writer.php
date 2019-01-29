<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Support\Str;
use Box\Spout\Writer\WriterInterface;
use Illuminate\Contracts\Support\Arrayable;
use Nikazooz\Simplesheet\Writers\CSVWriter;
use Nikazooz\Simplesheet\Concerns\FromArray;
use Nikazooz\Simplesheet\Concerns\FromQuery;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Events\BeforeExport;
use Nikazooz\Simplesheet\Events\BeforeWriting;
use Nikazooz\Simplesheet\Concerns\FromIterator;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\MapsCsvSettings;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Nikazooz\Simplesheet\Writers\Factory as WriterFactory;

class Writer
{
    use HasEventBus, MapsCsvSettings;

    /**
     * @var object
     */
    private $exportable;

    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var \Box\Spout\Writer\WriterInterface
     */
    protected $spoutWriter;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * New Writer instance.
     *
     * @param  string  $tempPath
     * @param  int  $chunkSize
     * @param  array  $csvSettings
     * @return void
     */
    public function __construct(string $tempPath, int $chunkSize, array $csvSettings = [])
    {
        $this->tmpPath = $tempPath;
        $this->chunkSize = $chunkSize;
        $this->applyCsvSettings($csvSettings);
    }

    /**
     * @param  object  $export
     * @param  string  $writerType
     * @param  string|null  $tempFile
     * @return string
     */
    public function export($export, string $writerType, string $tempFile = null): string
    {
        if ($export instanceof WithEvents) {
            $this->registerListeners($export->registerEvents());
        }

        $this->exportable = $export;
        $fileName = $tempFile ?? $this->tempFile();

        $this->raise(new BeforeExport($this, $this->exportable));

        $this->spoutWriter = WriterFactory::create($writerType);

        $this->raise(new BeforeWriting($this, $this->exportable));

        $this->configureCsvWriter();
        $this->spoutWriter->openToFile($fileName);

        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        foreach ($sheetExports as $sheetIndex => $sheetExport) {
            $this->addNewSheet($sheetIndex)->export($sheetExport);
        }

        $this->spoutWriter->close();
        unset($this->spoutWriter);

        return $fileName;
    }

    /**
     * @return string
     */
    public function tempFile(): string
    {
        return $this->tmpPath . DIRECTORY_SEPARATOR . 'laravel-simplesheet-' . Str::random(16);
    }

    /**
     * @return void
     */
    protected function configureCsvWriter()
    {
        if ($this->spoutWriter instanceof CSVWriter) {
            $this->spoutWriter->setFieldDelimiter($this->delimiter);
            $this->spoutWriter->setFieldEnclosure($this->enclosure);
            $this->spoutWriter->setLineEnding($this->lineEnding);
            $this->spoutWriter->setShouldAddBOM($this->useBom);
            $this->spoutWriter->setIncludeSeparatorLine($this->includeSeparatorLine);
            $this->spoutWriter->setExcelCompatibility($this->excelCompatibility);
        }
    }

    /**
     * @param  int|null  $sheetIndex
     * @return \Nikazooz\Simplesheet\Sheet
     *
     * @throws \Exception
     */
    public function addNewSheet(int $sheetIndex = null)
    {
        if (! $this->spoutWriter) {
            throw new \Exception('Writer must be opened first!');
        }

        return new Sheet($this->spoutWriter, $sheetIndex, $this->chunkSize);
    }

    /**
     * @param  string  $delimiter
     * @return \Nikazooz\Simplesheet\Writer
     */
    public function setDelimiter(string $delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @param  string  $enclosure
     * @return \Nikazooz\Simplesheet\Writer
     */
    public function setEnclosure(string $enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * @param  string  $lineEnding
     * @return \Nikazooz\Simplesheet\Writer
     */
    public function setLineEnding(string $lineEnding)
    {
        $this->lineEnding = $lineEnding;

        return $this;
    }

    /**
     * @param  bool  $includeSeparatorLine
     * @return \Nikazooz\Simplesheet\Writer
     */
    public function setIncludeSeparatorLine(bool $includeSeparatorLine)
    {
        $this->includeSeparatorLine = $includeSeparatorLine;

        return $this;
    }

    /**
     * @param  bool  $excelCompatibility
     * @return \Nikazooz\Simplesheet\Writer
     */
    public function setExcelCompatibility(bool $excelCompatibility)
    {
        $this->excelCompatibility = $excelCompatibility;

        return $this;
    }
}
