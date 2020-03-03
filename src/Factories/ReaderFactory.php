<?php

namespace Nikazooz\Simplesheet\Factories;

use Box\Spout\Common\Type;
use Box\Spout\Reader\CSV\Reader as CsvReader;
use Box\Spout\Reader\ReaderFactory as SpoutReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Nikazooz\Simplesheet\Concerns\MapsCsvSettings;
use Nikazooz\Simplesheet\Concerns\WithCustomCsvSettings;
use Nikazooz\Simplesheet\Simplesheet;

class ReaderFactory
{
    use MapsCsvSettings;

    /**
     * @param  string  $type
     * @param  object  $import
     * @return \Box\Spout\Reader\ReaderInterface
     */
    public static function make($type, $import): ReaderInterface
    {
        return static::configureReader(static::makeUnconfiguredReader($type), $import);
    }

    /**
     * @param  string  $type
     * @return \Box\Spout\Reader\ReaderInterface
     */
    protected static function makeUnconfiguredReader($type)
    {
        if (Simplesheet::TSV === $type) {
            return SpoutReaderFactory::create(Type::CSV)
                ->setFieldDelimiter("\t")
                ->setShouldPreserveEmptyRows(true);
        }

        return SpoutReaderFactory::create($type)->setShouldPreserveEmptyRows(true);
    }

    /**
     * @param  \Box\Spout\Reader\ReaderInterface  $reader
     * @param  object  $import
     * @return \Box\Spout\Reader\ReaderInterface
     */
    protected static function configureReader(ReaderInterface $reader, $import): ReaderInterface
    {
        if ($import instanceof WithCustomCsvSettings) {
            static::applyCsvSettings($import->getCsvSettings());
        }

        if ($reader instanceof CsvReader) {
            $reader->setFieldDelimiter(static::$delimiter);
            $reader->setFieldEnclosure(static::$enclosure);
            $reader->setEncoding(static::$inputEncoding);
        }

        return $reader;
    }
}
