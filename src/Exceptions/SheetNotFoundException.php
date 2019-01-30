<?php

namespace Nikazooz\Simplesheet\Exceptions;

class SheetNotFoundException extends \Exception
{
    /**
     * @param  string  $name
     * @return \Nikazooz\Simplesheet\Exceptions\SheetNotFoundException
     */
    public static function byName(string $name)
    {
        return new static("Your requested sheet name [{$name}] is out of bounds.");
    }

    /**
     * @param  int  $index
     * @param  int  $sheetCount
     * @return \Nikazooz\Simplesheet\Exceptions\SheetNotFoundException
     */
    public static function byIndex(int $index, int $sheetCount)
    {
        return new static("Your requested sheet index: {$index} is out of bounds. The actual number of sheets is {$sheetCount}.");
    }
}
