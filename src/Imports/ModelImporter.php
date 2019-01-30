<?php

namespace Nikazooz\Simplesheet\Imports;

use Box\Spout\Reader\SheetInterface;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Concerns\WithMapping;
use Nikazooz\Simplesheet\Concerns\WithBatchInserts;

class ModelImporter
{
    /**
     * @var \Nikazooz\Simplesheet\Imports\ModelManager
     */
    private $manager;

    /**
     * @param  \Nikazooz\Simplesheet\Imports\ModelManager  $manager
     * @return void
     */
    public function __construct(ModelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param  \Box\Spout\Reader\SheetInterface  $sheet
     * @param  \Nikazooz\Simplesheet\Concerns\ToModel  $import
     * @param  int|null  $startRow
     */
    public function import(SheetInterface $sheet, ToModel $import, int $startRow = 1)
    {
        $headingRow = HeadingRowExtractor::extract($sheet, $import);
        $batchSize = $import instanceof WithBatchInserts ? $import->batchSize() : 1;
        $endRow = EndRowFinder::find($import, $startRow);

        $i = 0;
        foreach ($sheet->getRowIterator() as $rowNumber => $spreadSheetRow) {
            if ($rowNumber < $startRow) {
                continue;
            }

            if (null !== $endRow && $rowNumber > $endRow) {
                break;
            }

            $i++;

            $rowArray = $this->mapRow($spreadSheetRow, $headingRow);

            if ($import instanceof WithMapping) {
                $rowArray = $import->map($rowArray);
            }

            $this->manager->add(
                $rowNumber,
                $rowArray
            );

            // Flush each batch.
            if (($i % $batchSize) === 0) {
                $this->manager->flush($import, $batchSize > 1);
                $i = 0;
            }
        }

        // Flush left-overs.
        $this->manager->flush($import, $batchSize > 1);
    }

    /**
     * @param  array  $row
     * @param  array  $headingRow
     * @return array
     */
    protected function mapRow($row, $headingRow)
    {
        $cells = [];

        foreach ($row as $i => $value) {
            if (isset($headingRow[$i])) {
                $cells[$headingRow[$i]] = $value;
            } else {
                $cells[] = $value;
            }
        }

        return $cells;
    }
}
