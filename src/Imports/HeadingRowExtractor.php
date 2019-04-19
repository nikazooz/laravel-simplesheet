<?php

namespace Nikazooz\Simplesheet\Imports;

use Nikazooz\Simplesheet\Row;
use Box\Spout\Reader\SheetInterface;
use Nikazooz\Simplesheet\Concerns\WithStartRow;
use Nikazooz\Simplesheet\Concerns\WithHeadingRow;

class HeadingRowExtractor
{
    /**
     * @const int
     */
    const DEFAULT_HEADING_ROW = 1;

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithHeadingRow|mixed  $importable
     * @return int
     */
    public static function headingRow($importable): int
    {
        return method_exists($importable, 'headingRow')
            ? $importable->headingRow()
            : self::DEFAULT_HEADING_ROW;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithHeadingRow|mixed  $importable
     * @return int
     */
    public static function determineStartRow($importable): int
    {
        if ($importable instanceof WithStartRow) {
            return $importable->startRow();
        }

        // The start row is the row after the heading row if we have one!
        return $importable instanceof WithHeadingRow
            ? self::headingRow($importable) + 1
            : self::DEFAULT_HEADING_ROW;
    }

    /**
     * @param  \Box\Spout\Reader\SheetInterface  $sheet
     * @param  \Nikazooz\Simplesheet\Concerns\WithHeadingRow|mixed  $importable
     * @return array
     */
    public static function extract(SheetInterface $sheet, $importable): array
    {
        if (! $importable instanceof WithHeadingRow) {
            return [];
        }

        $headingRowNumber = self::headingRow($importable);

        $rows = [];
        foreach ($sheet->getRowIterator() as $rowNumber => $row) {
            if ($rowNumber < $headingRowNumber) {
                continue;
            }

            if ($rowNumber > $headingRowNumber) {
                break;
            }

            $rows[] = $row;
        }

        $headingRow = head($rows);

        return HeadingRowFormatter::format($headingRow);
    }
}
