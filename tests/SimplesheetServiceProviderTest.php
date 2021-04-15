<?php

namespace Nikazooz\Simplesheet\Tests;

use Nikazooz\Simplesheet\Simplesheet;

class SimplesheetServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function is_bound()
    {
        $this->assertTrue($this->app->bound('simplesheet'));
    }

    /**
     * @test
     */
    public function has_aliased()
    {
        $this->assertTrue($this->app->isAlias(Simplesheet::class));
        $this->assertEquals('simplesheet', $this->app->getAlias(Simplesheet::class));
    }
}
