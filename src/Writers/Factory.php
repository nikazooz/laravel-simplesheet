<?php

namespace Nikazooz\Simplesheet\Writers;

use Box\Spout\Writer\WriterFactory;
use Nikazooz\Simplesheet\Simplesheet;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;

class Factory
{
    public static function create($type)
    {
        if (Simplesheet::CSV === $type) {
            $writer = new CSVWriter();
            $writer->setGlobalFunctionsHelper(new GlobalFunctionsHelper());
            return $writer;
        }

        return WriterFactory::create($type);
    }
}
