<?php

namespace Nikazooz\Simplesheet\Tests;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Nikazooz\Simplesheet\Fakes\SimplesheetFake;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Nikazooz\Simplesheet\Facades\Simplesheet as SimplesheetFacade;

class SimplesheetFakeTest extends TestCase
{
    /**
     * @test
     */
    public function can_fake_an_export()
    {
        SimplesheetFacade::fake();

        // Simplesheet instance should be swapped to the fake now.
        $this->assertInstanceOf(SimplesheetFake::class, $this->app->make('simplesheet'));
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_downloaded_export()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::download($this->givenExport(), 'downloaded-filename.csv');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        SimplesheetFacade::assertDownloaded('downloaded-filename.csv');
        SimplesheetFacade::assertDownloaded('downloaded-filename.csv', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_stored_export()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::store($this->givenExport(), 'stored-filename.csv', 's3');

        $this->assertTrue($response);

        SimplesheetFacade::assertStored('stored-filename.csv', 's3');
        SimplesheetFacade::assertStored('stored-filename.csv', 's3', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function a_callback_can_be_passed_as_the_second_argument_when_asserting_against_a_faked_stored_export()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::store($this->givenExport(), 'stored-filename.csv');

        $this->assertTrue($response);

        SimplesheetFacade::assertStored('stored-filename.csv');
        SimplesheetFacade::assertStored('stored-filename.csv', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_export()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::queue($this->givenExport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        SimplesheetFacade::assertQueued('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_import()
    {
        SimplesheetFacade::fake();

        SimplesheetFacade::import($this->givenImport(), 'stored-filename.csv', 's3');

        SimplesheetFacade::assertImported('stored-filename.csv', 's3');
        SimplesheetFacade::assertImported('stored-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_import()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::queueImport($this->givenImport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        SimplesheetFacade::assertQueued('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
    }

    /**
     * @test
     */
    public function a_callback_can_be_passed_as_the_second_argument_when_asserting_against_a_faked_queued_export()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::queue($this->givenExport(), 'queued-filename.csv');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        SimplesheetFacade::assertQueued('queued-filename.csv');
        SimplesheetFacade::assertQueued('queued-filename.csv', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @return FromCollection
     */
    private function givenExport()
    {
        return new class implements FromCollection {
            /**
             * @return Collection
             */
            public function collection()
            {
                return collect(['foo', 'bar']);
            }
        };
    }

    /**
     * @return object
     */
    private function givenImport()
    {
        return new class implements ToModel, ShouldQueue {
            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([]);
            }
        };
    }
}
