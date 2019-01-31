<?php

namespace Nikazooz\Simplesheet\Concerns;

use Iterator;

interface FromIterator
{
    /**
     * @return \Iterator
     */
    public function iterator(): Iterator;
}
