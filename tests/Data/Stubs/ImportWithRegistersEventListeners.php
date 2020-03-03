<?php

namespace Nikazooz\Simplesheet\Tests\Data\Stubs;

use Nikazooz\Simplesheet\Concerns\Importable;
use Nikazooz\Simplesheet\Concerns\RegistersEventListeners;
use Nikazooz\Simplesheet\Concerns\WithEvents;

class ImportWithRegistersEventListeners implements WithEvents
{
    use Importable, RegistersEventListeners;

    /**
     * @var callable
     */
    public static $beforeImport;

    /**
     * @var callable
     */
    public static $afterImport;

    /**
     * @var callable
     */
    public static $beforeTransactionCommit;

    /**
     * @var callable
     */
    public static $beforeSheet;

    /**
     * @var callable
     */
    public static $afterSheet;

    public static function beforeImport()
    {
        (static::$beforeImport)(...func_get_args());
    }

    public static function afterImport()
    {
        (static::$afterImport)(...func_get_args());
    }

    public static function beforeTransactionCommit()
    {
        (static::$beforeTransactionCommit)(...func_get_args());
    }

    public static function beforeSheet()
    {
        (static::$beforeSheet)(...func_get_args());
    }

    public static function afterSheet()
    {
        (static::$afterSheet)(...func_get_args());
    }
}
