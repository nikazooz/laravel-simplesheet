# Laravel Simplesheet

Wrapper around [Box Spout](http://opensource.box.com/spout/) with the goal of simplifying exports and imports in Laravel.

## Credits

This package uses a lot of code copied from [Laravel Excel](https://laravel-excel.maatwebsite.nl) so a big thanks to the Laravel Excel team for their work on that amazing package.

## Rationale

Laravel Excel is an amazing package and I highly recommend it. One problem I've had with it comes from using [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/): working with really big datasets requires a lot of memory. Some of the features provided by PhpSpreadsheet require it to hold entire document represented by objects in memory.
