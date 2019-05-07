<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Nikazooz\Simplesheet\Writer;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Concerns\WithTitle;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\WithEvents;
use Nikazooz\Simplesheet\Events\BeforeWriting;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\RegistersEventListeners;

class SheetWith100Rows implements FromCollection, WithEvents, WithTitle
{
    use Exportable, RegistersEventListeners;

    /**
     * @var string
     */
    private $title;

    /**
     * @param  string  $title
     * @return void
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $collection = new Collection;
        for ($i = 0; $i < 100; $i++) {
            $row = new Collection();
            for ($j = 0; $j < 5; $j++) {
                $row[] = $i.'-'.$j;
            }

            $collection->push($row);
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @param  \Nikazooz\Simplesheet\Events\BeforeWriting  $event
     * @return void
     */
    public static function beforeWriting(BeforeWriting $event)
    {
        TestCase::assertInstanceOf(Writer::class, $event->writer);
    }
}
