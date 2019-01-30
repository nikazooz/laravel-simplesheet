<?php

namespace Nikazooz\Simplesheet\Events;

class AfterSheet extends Event
{
    /**
     * @var \Nikazooz\Simplesheet\Sheet|\Nikazooz\Simplesheet\Imports\Sheet
     */
    public $sheet;

    /**
     * @var object
     */
    private $exportable;

    /**
     * @param  \Nikazooz\Simplesheet\Sheet|\Nikazooz\Simplesheet\Imports\Sheet  $sheet
     * @param  object  $exportable
     * @return void
     */
    public function __construct($sheet, $exportable)
    {
        $this->sheet = $sheet;
        $this->exportable = $exportable;
    }

    /**
     * @return \Nikazooz\Simplesheet\Sheet|\Nikazooz\Simplesheet\Imports\Sheet
     */
    public function getSheet()
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
}
