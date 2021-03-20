<?php

namespace Nikazooz\Simplesheet\Imports;

use Box\Spout\Reader\SheetInterface;
use Nikazooz\Simplesheet\Concerns\WithMapping;

trait ProcessesRows
{
    /**
     * @param  object  $startRow
     * @param  \Box\Spout\Reader\SheetInterface  $sheet
     * @param  int|null $startRow
     * @return \Generator<array>
     */
    protected function iterateMappedImportRows($import, SheetInterface $sheet, int $startRow = null)
    {
        $headingRow = HeadingRowExtractor::extract($sheet, $import);
        $startRow = $startRow ?? 1;
        $endRow = EndRowFinder::find($import, $startRow);

        foreach ($this->iterateRowsBetween($sheet, $startRow, $endRow) as $rowNumber => $row) {
            $row = $this->mapRowToHeading($row, $headingRow);

            if ($import instanceof WithMapping) {
                $row = $import->map($row);
            }

            yield $rowNumber => $row;
        }
    }

    /**
     * @param  \Box\Spout\Reader\SheetInterface  $sheet
     * @param  int  $startRow
     * @param  int|null $endRow
     * @return \Generator<array>
     */
    protected function iterateRowsBetween($sheet, int $startRow, int $endRow = null)
    {
        foreach ($sheet->getRowIterator() as $rowNumber => $row) {
            if ($rowNumber < $startRow) {
                continue;
            }

            if (null !== $endRow && $rowNumber > $endRow) {
                break;
            }

            yield $rowNumber => $row->toArray();
        }
    }

    /**
     * @param  array  $row
     * @param  array  $headingRow
     * @return array
     */
    protected function mapRowToHeading($row, $headingRow)
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
