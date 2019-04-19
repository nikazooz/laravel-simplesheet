<?php

namespace Nikazooz\Simplesheet\Factories;

use Box\Spout\Writer\WriterInterface;
use Nikazooz\Simplesheet\Simplesheet;
use Nikazooz\Simplesheet\Writers\CsvWriter;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Nikazooz\Simplesheet\Concerns\MapsCsvSettings;
use Nikazooz\Simplesheet\Concerns\WithCustomCsvSettings;
use Box\Spout\Writer\WriterFactory as SpoutWriterFactory;

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

        return SpoutWriterFactory::create($type);
    }

    /**
     * @return \Nikazooz\Simplesheet\Writers\CSVWriter
     */
    protected static function makeCsvWriter($type, $export): WriterInterface
    {
        $writer = (new CsvWriter())->setGlobalFunctionsHelper(new GlobalFunctionsHelper());

        static::applyCsvSettings(config('simplesheet.exports.csv', []));

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
