<?php

namespace Nikazooz\Simplesheet\Concerns;

interface SkipsUnknownSheets
{
    /**
     * @param  string|int  $sheetName
     * @return void
     */
    public function onUnknownSheet($sheetName);
}
