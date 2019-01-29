<?php

namespace Nikazooz\Simplesheet\Tests\Concerns;

use Nikazooz\Simplesheet\Tests\TestCase;
use Nikazooz\Simplesheet\Tests\Data\Stubs\Database\User;
use Nikazooz\Simplesheet\Tests\Data\Stubs\FromUsersQueryExport;

class FromQueryTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');

        factory(User::class, 100)->create([]);
    }

    /**
     * @test
     */
    public function can_export_from_query()
    {
        $export = new FromUsersQueryExport;

        $response = $export->store('from-query-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-query-store.xlsx', 'xlsx');

        $allUsers = $export->query()->get()->map(function (User $user) {
            return array_values($user->toArray());
        })->toArray();

        $this->assertEquals($allUsers, $contents);
    }
}
