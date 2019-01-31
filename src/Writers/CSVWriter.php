<?php

namespace Nikazooz\Simplesheet\Writers;

use Box\Spout\Writer\CSV\Writer;
use Box\Spout\Common\Exception\IOException;

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

    public function setLineEnding($lineEnding)
    {
        $this->lineEnding = $lineEnding;

        return $this;
    }

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
                'sep=' . $this->fieldDelimiter . $this->lineEnding
            );
        }
    }

    /**
     * Adds data to the currently opened writer.
     *
     * @param  array $dataRow Array containing data to be written.
     *          Example $dataRow = ['data1', 1234, null, '', 'data5'];
     * @param \Box\Spout\Writer\Style\Style $style Ignored here since CSV does not support styling.
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If unable to write data
     */
    protected function addRowToWriter(array $dataRow, $style)
    {
        if (PHP_EOL !== $this->lineEnding) {
            return $this->customAddRowToWriter($dataRow);
        }

        parent::addRowToWriter($dataRow, $style);
    }

   /**
     * Adds data to the currently opened writer.
     *
     * @param  array $dataRow Array containing data to be written.
     *          Example $dataRow = ['data1', 1234, null, '', 'data5'];
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If unable to write data
     */
    protected function customAddRowToWriter($dataRow)
    {
        $wasWriteSuccessful = $this->globalFunctionsHelper->fputs(
            $this->filePointer,
            $this->prepareRowForWriting($dataRow)
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
     * @param  array  $row
     * @return string
     */
    protected function prepareRowForWriting($row)
    {
        return implode($this->fieldDelimiter, array_map(function ($cell) {
            return $this->encloseString($cell);
        }, $row)) . $this->lineEnding;
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
            $this->fieldEnclosure,
            addslashes($str),
            $this->fieldEnclosure,
        ]);
    }
}
