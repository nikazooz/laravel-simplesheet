<?php

namespace Nikazooz\Simplesheet\Helpers;

class NumberFormat
{
    const FORMAT_GENERAL = 'General';

    const FORMAT_TEXT = '@';

    const FORMAT_NUMBER = '0';
    const FORMAT_NUMBER_00 = '0.00';
    const FORMAT_NUMBER_COMMA_SEPARATED1 = '#,##0.00';
    const FORMAT_NUMBER_COMMA_SEPARATED2 = '#,##0.00_-';

    const FORMAT_PERCENTAGE = '0%';
    const FORMAT_PERCENTAGE_00 = '0.00%';

    const FORMAT_DATE_YYYYMMDD2 = 'yyyy-mm-dd';
    const FORMAT_DATE_YYYYMMDD = 'yyyy-mm-dd';
    const FORMAT_DATE_DDMMYYYY = 'dd/mm/yyyy';
    const FORMAT_DATE_DMYSLASH = 'd/m/yy';
    const FORMAT_DATE_DMYMINUS = 'd-m-yy';
    const FORMAT_DATE_DMMINUS = 'd-m';
    const FORMAT_DATE_MYMINUS = 'm-yy';
    const FORMAT_DATE_XLSX14 = 'mm-dd-yy';
    const FORMAT_DATE_XLSX15 = 'd-mmm-yy';
    const FORMAT_DATE_XLSX16 = 'd-mmm';
    const FORMAT_DATE_XLSX17 = 'mmm-yy';
    const FORMAT_DATE_XLSX22 = 'm/d/yy h:mm';
    const FORMAT_DATE_DATETIME = 'd/m/yy h:mm';
    const FORMAT_DATE_TIME1 = 'h:mm AM/PM';
    const FORMAT_DATE_TIME2 = 'h:mm:ss AM/PM';
    const FORMAT_DATE_TIME3 = 'h:mm';
    const FORMAT_DATE_TIME4 = 'h:mm:ss';
    const FORMAT_DATE_TIME5 = 'mm:ss';
    const FORMAT_DATE_TIME6 = 'h:mm:ss';
    const FORMAT_DATE_TIME7 = 'i:s.S';
    const FORMAT_DATE_TIME8 = 'h:mm:ss;@';
    const FORMAT_DATE_YYYYMMDDSLASH = 'yyyy/mm/dd;@';

    const FORMAT_CURRENCY_USD_SIMPLE = '$#,##0.00_-';
    const FORMAT_CURRENCY_USD = '$#,##0_-';
    const FORMAT_CURRENCY_EUR_SIMPLE = '#,##0.00_-€';
    const FORMAT_CURRENCY_EUR = '#,##0_-€';
    const FORMAT_ACCOUNTING_USD = '_($* #,##0.00_);_($* \(#,##0.00\);_($* -??_);_(@_)';
    const FORMAT_ACCOUNTING_EUR = '_(€* #,##0.00_);_(€* \(#,##0.00\);_(€* -??_);_(@_)';
}
