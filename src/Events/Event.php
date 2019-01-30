<?php

namespace Nikazooz\Simplesheet\Events;

abstract class Event
{
    /**
     * @return object
     */
    abstract public function getConcernable();
}
