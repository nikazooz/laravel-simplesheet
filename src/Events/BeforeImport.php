<?php

namespace Nikazooz\Simplesheet\Events;

use Nikazooz\Simplesheet\Reader;

class BeforeImport extends Event
{
    /**
     * @var \Nimazooz\Simplesheet\Reader
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
     * @return \Nimazooz\Simplesheet\Reader
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
