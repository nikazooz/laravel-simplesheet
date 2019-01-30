<?php

namespace Nikazooz\Simplesheet\Events;

use Nikazooz\Simplesheet\Writer;

class BeforeWriting extends Event
{
    /**
     * @var \Nikazooz\Simplesheet\Writer
     */
    public $writer;

    /**
     * @var object
     */
    private $exportable;

    /**
     * @param  \Nikazooz\Simplesheet\Writer  $writer
     * @param  object  $exportable
     */
    public function __construct(Writer $writer, $exportable)
    {
        $this->writer = $writer;
        $this->exportable = $exportable;
    }

    /**
     * @return \Nikazooz\Simplesheet\Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }

    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->exportable;
    }
}
