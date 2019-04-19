<?php

namespace Nikazooz\Simplesheet\Imports;

use Box\Spout\Reader\SheetInterface;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Concerns\WithBatchInserts;

class ModelImporter
{
    use ProcessesRows;

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
        $batchSize = $import instanceof WithBatchInserts ? $import->batchSize() : 1;

        $i = 0;
        foreach ($this->iterateMappedImportRows($import, $sheet, $startRow) as $rowNumber => $row) {
            $i++;

            $this->manager->add($rowNumber, $row);

            // Flush each batch.
            if (($i % $batchSize) === 0) {
                $this->manager->flush($import, $batchSize > 1);
                $i = 0;
            }
        }

        // Flush left-overs.
        $this->manager->flush($import, $batchSize > 1);
    }
}
