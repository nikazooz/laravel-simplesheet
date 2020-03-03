<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Exception;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Concerns\Exportable;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\WithMapping;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

class QueuedExportWithFailedHook implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @var bool
     */
    public $failed = false;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            new User([
                'name' => 'John Doe',
            ]),
        ]);
    }

    /**
     * @param  \Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User  $user
     * @return array
     */
    public function map($user): array
    {
        throw new Exception('we expect this');
    }

    /**
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Assert::assertEquals('we expect this', $exception->getMessage());

        app()->bind('queue-has-failed', function () {
            return true;
        });
    }
}
