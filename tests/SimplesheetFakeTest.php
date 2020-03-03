<?php

namespace Nikazooz\Simplesheet\Tests;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Nikazooz\Simplesheet\Concerns\FromCollection;
use Nikazooz\Simplesheet\Concerns\ToModel;
use Nikazooz\Simplesheet\Facades\Simplesheet as SimplesheetFacade;
use Nikazooz\Simplesheet\Fakes\SimplesheetFake;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertDownloaded('/\w{10}-\w{8}\.csv/');
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
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertStored('/\w{6}-\w{8}\.csv/', 's3');
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
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertStored('/\w{6}-\w{8}\.csv/');
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
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertStored('/\w{6}-\w{8}\.csv/', 's3');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_implicitly_queued_export()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::store($this->givenQueuedExport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        SimplesheetFacade::assertStored('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertQueued('/\w{6}-\w{8}\.csv/', 's3');
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
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertImported('/\w{6}-\w{8}\.csv/', 's3');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_import_with_uploaded_file()
    {
        SimplesheetFacade::fake();

        SimplesheetFacade::import($this->givenImport(), $this->givenUploadedFile(__DIR__.'/Data/Disks/Local/import.xlsx'));

        SimplesheetFacade::assertImported('import.xlsx');
        SimplesheetFacade::assertImported('import.xlsx', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertImported('/\w{6}\.xlsx/');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_import()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::queueImport($this->givenQueuedImport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        SimplesheetFacade::assertImported('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertQueued('/\w{6}-\w{8}\.csv/', 's3');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_implicitly_queued_import()
    {
        SimplesheetFacade::fake();

        $response = SimplesheetFacade::import($this->givenQueuedImport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        SimplesheetFacade::assertImported('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3');
        SimplesheetFacade::assertQueued('queued-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertQueued('/\w{6}-\w{8}\.csv/', 's3');
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
        SimplesheetFacade::matchByRegex();
        SimplesheetFacade::assertQueued('/\w{6}-\w{8}\.csv/');
    }

    /**
     * @return \Nikazooz\Simplesheet\Concerns\FromCollection
     */
    private function givenExport()
    {
        return new class implements FromCollection {
            /**
             * @return \Illuminate\Support\Collection
             */
            public function collection()
            {
                return collect(['foo', 'bar']);
            }
        };
    }

    /**
     * @return \Nikazooz\Simplesheet\Concerns\FromCollection
     */
    private function givenQueuedExport()
    {
        return new class implements FromCollection, ShouldQueue {
            /**
             * @return \Illuminate\Support\Collection
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
        return new class implements ToModel {
            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model|null
             */
            public function model(array $row)
            {
                return new User([]);
            }
        };
    }

    /**
     * @return object
     */
    private function givenQueuedImport()
    {
        return new class implements ToModel, ShouldQueue {
            /**
             * @param  array  $row
             * @return \Illuminate\Database\Eloquent\Model|null
             */
            public function model(array $row)
            {
                return new User([]);
            }
        };
    }
}
