# Laravel Simplesheet

Wrapper around [Box Spout](http://opensource.box.com/spout/) with the goal of simplifying exports and imports in [Laravel](https://laravel.com).

<h4 align="center">
  <a href="https://nikazooz.github.io/laravel-simplesheet/1.x/exports/">Quickstart</a>
  <span> · </span>
  <a href="https://nikazooz.github.io/laravel-simplesheet/1.x/getting-started/">Documentation</a>
  <span> · </span>
  <a href="https://nikazooz.github.io/laravel-simplesheet/1.x/getting-started/contributing.html">Contributing</a>
  <span> · </span>
  <a href="https://nikazooz.github.io/laravel-simplesheet/1.x/getting-started/support.html">Support</a>
</h4>


## Credits

This package uses a lot of code copied from [Laravel Excel](https://laravel-excel.maatwebsite.nl) and probably wouldn't exist without it, so a big thanks to the Laravel Excel team for their work on that amazing package. Make sure to check it out!


## Rationale

Laravel Excel is an amazing package and I highly recommend it. One problem I've had with it comes from using [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/): working with really big datasets requires a lot of memory, even when the exports/imports are chunked. Some of the features provided by PhpSpreadsheet, like cell mapping and formulas, require it to hold entire document represented by objects in memory.


## ✨ Features

- **Easily export** from different sources: an array, Laravel Collection or query, to different [supported formats](https://nikazooz.github.io/laravel-simplesheet/1.x/exports/export-formats.html)

- **Supercharged imports** import of workbooks and worksheets to Eloquent models with batch inserts! Have large files? Your entire import can happen in the background. If you like, you can even handle each row youself!

- **Memory efficient.** Using Box Spout allows this package to use considerably less memory than some alternatives.

Compared to Laravel Excel, this package provides less features because it uses a different library for working with spreadsheets under the hood. However it assures that exports and imports are fast and require less memory.

## 🎓 Using Laravel Simplesheet

You can find the full documentation of Laravel Simplesheet [on the website](https://nikazooz.github.io/laravel-simplesheet).

Suggestions for improving the docs are welcome. The documentation repository can be found at [https://github.com/nikazooz/laravel-simplesheet-docs](https://github.com/nikazooz/laravel-simplesheet-docs).


## License

This software is open source and licensed under the [MIT license](https://choosealicense.com/licenses/mit/).


## :wrench: Supported Versions

Versions will be supported for a limited amount of time.

| Version | Laravel Version | Php Version | Box Spout | Support                |
| ------- | --------------- | ----------- | --------- | ---------------------- |
| 1.*     | 5.5 - 8.*       | ^7.1        | ^2.7      | Bug and security fixes |
