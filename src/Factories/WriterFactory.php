<?php

namespace Nikazooz\Simplesheet\Factories;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\CSV\Manager\OptionsManager;
use Box\Spout\Writer\Common\Creator\WriterFactory as SpoutWriterFactory;
use Box\Spout\Writer\WriterInterface;
use Nikazooz\Simplesheet\Concerns\MapsCsvSettings;
use Nikazooz\Simplesheet\Concerns\WithCustomCsvSettings;
use Nikazooz\Simplesheet\Simplesheet;
use Nikazooz\Simplesheet\Writers\CsvWriter;

class WriterFactory
{
    use MapsCsvSettings;

    const CUSTOM_CSV_WRITER = [
        Simplesheet::CSV,
        Simplesheet::TSV,
    ];

    /**
     * @param  string  $type
     * @param  object  $export
     * @return \Box\Spout\Writer\WriterInterface
     */
    public static function make($type, $export): WriterInterface
    {
        if (in_array($type, static::CUSTOM_CSV_WRITER)) {
            return static::makeCsvWriter($type, $export);
        }

        return SpoutWriterFactory::createFromType($type);
    }

    /**
     * @return \Nikazooz\Simplesheet\Writers\CsvWriter
     */
    protected static function makeCsvWriter($type, $export): WriterInterface
    {
        $writer = (new CsvWriter(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new HelperFactory()
        ));

        static::applyCsvSettings(static::getCsvConfig());

        if (Simplesheet::TSV === $type) {
            $writer->setFieldDelimiter("\t");
        }

        if ($export instanceof WithCustomCsvSettings) {
            static::applyCsvSettings($export->getCsvSettings());
        }

        $writer->setFieldDelimiter(static::$delimiter);
        $writer->setFieldEnclosure(static::$enclosure);
        $writer->setLineEnding(static::$lineEnding);
        $writer->setShouldAddBOM(static::$useBom);
        $writer->setIncludeSeparatorLine(static::$includeSeparatorLine);
        $writer->setExcelCompatibility(static::$excelCompatibility);

        return $writer;
    }
}
