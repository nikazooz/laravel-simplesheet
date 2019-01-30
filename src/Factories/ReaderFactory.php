<?php

namespace Nikazooz\Simplesheet\Factories;

use Box\Spout\Common\Type;
use Nikazooz\Simplesheet\Simplesheet;
use Box\Spout\Reader\ReaderFactory as SpoutReaderFactory;

class ReaderFactory
{
    /**
     * @param  string  $type
     * @return \Box\Spout\Reader\ReaderInterface
     */
    public static function create($type)
    {
        if (Simplesheet::TSV === $type) {
            return SpoutReaderFactory::create(Type::CSV)
                ->setFieldDelimiter("\t")
                ->setShouldPreserveEmptyRows(true);
        }

        return SpoutReaderFactory::create($type)->setShouldPreserveEmptyRows(true);
    }
}
