<?php

namespace Nikazooz\Simplesheet\Events;

use Nikazooz\Simplesheet\Reader;

class AfterImport extends Event
{
    /**
     * @var \Nikazooz\Simplesheet\Reader
     */
    public $reader;

    /**
     * @var object
     */
    private $importable;

    /**
     * @param  \Nikazooz\Simplesheet\Reader  $reader
     * @param  object  $importable
     * @return void
     */
    public function __construct(Reader $reader, $importable)
    {
        $this->reader = $reader;
        $this->importable = $importable;
    }

    /**
     * @return \Nikazooz\Simplesheet\Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->importable;
    }
}
