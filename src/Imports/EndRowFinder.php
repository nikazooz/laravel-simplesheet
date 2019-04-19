<?php

namespace Nikazooz\Simplesheet\Imports;

use Nikazooz\Simplesheet\Concerns\WithLimit;

class EndRowFinder
{
    /**
     * @param  \Nikazooz\Simplesheet\Concerns\WithLimit|object  $import
     * @param  int  $startRow
     * @return int|null
     */
    public static function find($import, int $startRow = null)
    {
        if (! $import instanceof WithLimit) {
            return;
        }

        // When no start row given,
        // use the first row as start row.
        $startRow = $startRow ?? 1;

        // Subtract 1 row from the start row, so a limit
        // of 1 row, will have the same start and end row.
        return ($startRow - 1) + $import->limit();
    }
}
