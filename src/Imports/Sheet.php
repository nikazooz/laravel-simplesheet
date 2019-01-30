<?php

namespace Nikazooz\Simplesheet\Imports;

use Illuminate\Support\Collection;
use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\ReaderInterface;
use Nikazooz\Simplesheet\HasEventBus;
use Nikazooz\Simplesheet\Concerns\ToArray;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Events\AfterSheet;
use Nikazooz\Simplesheet\Concerns\OnEachRow;
use Nikazooz\Simplesheet\Events\BeforeSheet;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Concerns\WithMapping;
use Nikazooz\Simplesheet\Concerns\ToCollection;
use Nikazooz\Simplesheet\Concerns\WithHeadingRow;

class Sheet
{
    use HasEventBus, ProcessesRows;

    /**
     * @var \Box\Spout\Reader\SheetInterface
     */
    protected $sheet;

    /**
     * @param  \Box\Spout\Reader\SheetInterface  $sheet
     * @return void
     */
    public function __construct(SheetInterface $sheet)
    {
        $this->sheet = $sheet;
    }

     /**
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @param  string|int  $index
     *
     * @return \Nikazooz\Simplesheet\Imports\Sheet
     */
    public static function make(ReaderInterface $reader, $index)
    {
        if (is_numeric($index)) {
            return static::byIndex($reader, $index);
        }

        return static::byName($reader, $index);
    }

    /**
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @param  int  $index
     *
     * @return \Nikazooz\Simplesheet\Imports\Sheet
     */
    public static function byIndex(ReaderInterface $reader, int $index)
    {
        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getIndex() === $index) {
                return new static($sheet);
            }
        }
    }

    /**
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @param  string  $name
     *
     * @return \Nikazooz\Simplesheet\Imports\Sheet
     */
    public static function byName(ReaderInterface $reader, string $name)
    {
        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() === $name) {
                return new static($sheet);
            }
        }
    }

    /**
     * @param  object  $import
     * @param  int|null  $startRow
     */
    public function import($import, int $startRow = 1)
    {
        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        $this->raise(new BeforeSheet($this, $import));

        if ($import instanceof ToModel) {
            app(ModelImporter::class)->import($this->sheet, $import, $startRow);
        }

        if ($import instanceof ToCollection) {
            $import->collection($this->toCollection($import));
        }

        if ($import instanceof ToArray) {
            $import->array($this->toArray($import));
        }

        if ($import instanceof OnEachRow) {
            $startRow = $startRow ?? 1;

            foreach ($this->iterateMappedImportRows($import, $this->sheet, $startRow) as $row) {
                $import->onRow($row);
            }
        }

        $this->raise(new AfterSheet($this, $import));
    }

    /**
     * @param  object  $import
     * @param  int|null  $startRow
     * @return array
     */
    public function toArray($import, int $startRow = null)
    {
        $startRow = $startRow ?? $this->getStartRow($import);

        // We need rows with 0 based index and not row number as key
        $rows = [];
        foreach ($this->iterateMappedImportRows($import, $this->sheet, $startRow) as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  object  $import
     * @return \Illuminate\Support\Collection
     */
    public function toCollection($import): Collection
    {
        return new Collection(array_map(function (array $row) {
            return new Collection($row);
        }, $this->toArray($import)));
    }

    /**
     * @param  object  $sheetImport
     * @return int
     */
    public function getStartRow($sheetImport): int
    {
        return HeadingRowExtractor::determineStartRow($sheetImport);
    }
}
