<?php

namespace Nikazooz\Simplesheet\Writers;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\CSV\Writer;

class CsvWriter extends Writer
{
    /**
     * @var bool
     */
    protected $includeSeparatorLine = false;

    /**
     * @var bool
     */
    protected $excelCompatibility = false;

    /**
     * @var string
     */
    protected $lineEnding = PHP_EOL;

    /**
     * @param  bool  $includeSeparatorLine
     * @return $this
     */
    public function setIncludeSeparatorLine($includeSeparatorLine)
    {
        $this->includeSeparatorLine = $includeSeparatorLine;

        return $this;
    }

    /**
     * @param  string  $lineEnding
     * @return \Nikazooz\Simplesheet\Writers\CsvWriter
     */
    public function setLineEnding($lineEnding)
    {
        $this->lineEnding = $lineEnding;

        return $this;
    }

    /**
     * @param  bool  $excelCompatibility
     * @return \Nikazooz\Simplesheet\Writers\CsvWriter
     */
    public function setExcelCompatibility($excelCompatibility)
    {
        $this->excelCompatibility = $excelCompatibility;

        return $this;
    }

    /**
     * Opens the CSV streamer and makes it ready to accept data.
     *
     * @return void
     */
    protected function openWriter()
    {
        parent::openWriter();

        if ($this->excelCompatibility) {
            $this->setShouldAddBOM(true); //  Enforce UTF-8 BOM Header
            $this->setIncludeSeparatorLine(true); //  Set separator line
            $this->setFieldEnclosure('"'); //  Set enclosure to "
            $this->setFieldDelimiter(';'); //  Set delimiter to a semi-colon
            $this->setLineEnding("\r\n");
        }

        if ($this->includeSeparatorLine) {
            $this->globalFunctionsHelper->fputs(
                $this->filePointer,
                'sep='.$this->getFieldDelimiter().$this->lineEnding
            );
        }
    }

    /**
     * Adds data to the currently opened writer.
     *
     * @param  \Box\Spout\Common\Entity\Row $row
     * @param \Box\Spout\Writer\Style\Style $style Ignored here since CSV does not support styling.
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If unable to write data
     */
    protected function addRowToWriter(Row $row)
    {
        if (PHP_EOL !== $this->lineEnding) {
            return $this->customAddRowToWriter($row);
        }

        parent::addRowToWriter($row);
    }

    /**
     * Adds data to the currently opened writer.
     *
     * @param  \Box\Spout\Common\Entity\Row $row
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If unable to write data
     */
    protected function customAddRowToWriter(Row $row)
    {
        $wasWriteSuccessful = $this->globalFunctionsHelper->fputs(
            $this->filePointer,
            $this->prepareRowForWriting($row)
        );

        if ($wasWriteSuccessful === false) {
            throw new IOException('Unable to write data');
        }

        $this->lastWrittenRowIndex++;
        if ($this->lastWrittenRowIndex % self::FLUSH_THRESHOLD === 0) {
            $this->globalFunctionsHelper->fflush($this->filePointer);
        }
    }

    /**
     * @param  \Box\Spout\Common\Entity\Row $row
     * @return string
     */
    protected function prepareRowForWriting(Row $row)
    {
        return implode($this->getFieldDelimiter(), array_map(function ($cell) {
            return $this->encloseString($cell);
        }, $row->toArray())).$this->lineEnding;
    }

    /**
     * @param  string  $str
     * @return string
     */
    protected function encloseString($str)
    {
        if (! preg_match('/\s+/', $str)) {
            return $str;
        }

        return vsprintf('%s%s%s', [
            $this->getFieldEnclosure(),
            addslashes($str),
            $this->getFieldEnclosure(),
        ]);
    }

    protected function getFieldDelimiter()
    {
        return $this->optionsManager->getOption(Options::FIELD_DELIMITER);
    }

    protected function getFieldEnclosure()
    {
        return $this->optionsManager->getOption(Options::FIELD_ENCLOSURE);
    }
}
