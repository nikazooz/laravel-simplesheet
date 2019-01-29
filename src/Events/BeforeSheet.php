<?php

namespace Nikazooz\Simplesheet\Events;

use Nikazooz\Simplesheet\Sheet;

class BeforeSheet extends Event
{
    /**
     * @var \Nikazooz\Simplesheet\Sheet
     */
    public $sheet;

    /**
     * @var object
     */
    private $exportable;

    /**
     * @param  \Nikazooz\Simplesheet\Sheet  $sheet
     * @param  object  $exportable
     */
    public function __construct(Sheet $sheet, $exportable)
    {
        $this->sheet = $sheet;
        $this->exportable = $exportable;
    }

    /**
     * @return \Nikazooz\Simplesheet\Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->exportable;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->sheet;
    }
}
