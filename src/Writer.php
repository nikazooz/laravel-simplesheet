<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Support\Str;
use Box\Spout\Writer\WriterInterface;
use Nikazooz\Simplesheet\Writers\Sheet;
use Illuminate\Contracts\Support\Arrayable;
use Nikazooz\Simplesheet\Writers\CsvWriter;
use Nikazooz\Simplesheet\Concerns\FromArray;
use Nikazooz\Simplesheet\Concerns\FromQuery;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Events\BeforeExport;
use Nikazooz\Simplesheet\Files\TemporaryFile;
use Nikazooz\Simplesheet\Events\BeforeWriting;
use Nikazooz\Simplesheet\Concerns\FromIterator;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Factories\WriterFactory;
use Nikazooz\Simplesheet\Concerns\MapsCsvSettings;
use Nikazooz\Simplesheet\Files\RemoteTemporaryFile;
use Nikazooz\Simplesheet\Files\TemporaryFileFactory;
use Nikazooz\Simplesheet\Concerns\WithMultipleSheets;
use Nikazooz\Simplesheet\Concerns\WithCustomCsvSettings;

class Writer
{
    use HasEventBus, MapsCsvSettings;

    /**
     * @var TemporaryFileFactory
     */
    protected $temporaryFileFactory;

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
     * @param  TemporaryFileFactory  $temporaryFileFactory
     * @param  int  $chunkSize
     * @param  array  $csvSettings
     * @return void
     */
    public function __construct(TemporaryFileFactory $temporaryFileFactory, int $chunkSize, array $csvSettings = [])
    {
        $this->chunkSize = $chunkSize;
        $this->temporaryFileFactory = $temporaryFileFactory;

        $this->applyCsvSettings($csvSettings);
    }

    /**
     * @param  object  $export
     * @param  string  $writerType
     * @param  TemporaryFile|null  $temporaryFile
     * @return TemporaryFile
     */
    public function export($export, string $writerType, TemporaryFile $temporaryFile = null): TemporaryFile
    {
        $this->open($export, $writerType);

        $temporaryFile = $temporaryFile ?? $this->temporaryFileFactory->makeLocal();

        $this->write($export, $temporaryFile, $writerType);

        $this->cleanUp();

        return $temporaryFile;
    }

    /**
     * @param  object  $export
     * @param  string  $writerType
     * @return $this
     */
    private function open($export, $writerType)
    {
        if ($export instanceof WithEvents) {
            $this->registerListeners($export->registerEvents());
        }

        $this->raise(new BeforeExport($this, $export));

        $this->spoutWriter = WriterFactory::create($writerType);

        return $this;
    }

    /**
     * @param  object  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     * @return TemporaryFile
     */
    private function write($export, TemporaryFile $temporaryFile, string $writerType)
    {
        $this->throwExceptionIfWriterIsNotSet();

        $this->raise(new BeforeWriting($this, $export));

        if ($export instanceof WithCustomCsvSettings) {
            $this->applyCsvSettings($export->getCsvSettings());
        }

        $this->configureCsvWriter();
        $this->spoutWriter->openToFile($temporaryFile->getLocalPath());

        foreach ($this->getSheetExports($export) as $sheetIndex => $sheetExport) {
            $this->addNewSheet($sheetIndex)->export($sheetExport);
        }

        if ($temporaryFile instanceof RemoteTemporaryFile) {
            $temporaryFile->updateRemote();
        }

        return $temporaryFile;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithMultipleSheets|object  $export
     * @return array
     */
    private function getSheetExports($export)
    {
        if ($export instanceof WithMultipleSheets) {
            return $export->sheets();
        }

        return [$export];
    }

    /**
     * @return void
     */
    private function cleanUp()
    {
        $this->spoutWriter->close();
        unset($this->spoutWriter);
    }

    /**
     * @return void
     */
    protected function configureCsvWriter()
    {
        if ($this->spoutWriter instanceof CsvWriter) {
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
     * @return \Nikazooz\Simplesheet\Writers\Sheet
     *
     * @throws \Exception
     */
    public function addNewSheet(int $sheetIndex = null)
    {
        $this->throwExceptionIfWriterIsNotSet();

        return new Sheet($this->spoutWriter, $sheetIndex, $this->chunkSize);
    }

    /**
     * @throws \Exception
     */
    private function throwExceptionIfWriterIsNotSet()
    {
        if (! $this->spoutWriter) {
            throw new \Exception('Writer must be opened first!');
        }
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
