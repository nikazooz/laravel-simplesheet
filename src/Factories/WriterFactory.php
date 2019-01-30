<?php

namespace Nikazooz\Simplesheet\Factories;

use Nikazooz\Simplesheet\Simplesheet;
use Nikazooz\Simplesheet\Writers\CsvWriter;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Writer\WriterFactory as SpoutWriterFactory;

class WriterFactory
{
    /**
     * @param  string  $type
     * @return \Box\Spout\Writer\WriterInterface
     */
    public static function create($type)
    {
        if (Simplesheet::CSV === $type) {
            return static::makeCSVWriter();
        }

        if (Simplesheet::TSV === $type) {
            return static::makeCSVWriter()->setFieldDelimiter("\t");
        }

        return SpoutWriterFactory::create($type);
    }

    /**
     * @return \Nikazooz\Simplesheet\Writers\CSVWriter
     */
    protected static function makeCSVWriter()
    {
        return (new CsvWriter())->setGlobalFunctionsHelper(new GlobalFunctionsHelper());
    }
}
