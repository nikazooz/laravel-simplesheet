<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Nikazooz\Simplesheet\Events\AfterSheet;
use Nikazooz\Simplesheet\Events\AfterImport;
use Nikazooz\Simplesheet\Events\BeforeSheet;
use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Events\BeforeImport;

class ImportWithEvents implements WithEvents
{
    use Importable;

    /**
     * @var callable
     */
    public $beforeImport;

    /**
     * @var callable
     */
    public $beforeSheet;

    /**
     * @var callable
     */
    public $afterSheet;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => $this->beforeImport ?? function () {
            },
            AfterImport::class => $this->afterImport ?? function () {
            },
            BeforeSheet::class => $this->beforeSheet ?? function () {
            },
            AfterSheet::class => $this->afterSheet ?? function () {
            },
        ];
    }
}
