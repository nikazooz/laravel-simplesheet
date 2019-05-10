<?php

namespace Nikazooz\Simplesheet\Transactions;

use Illuminate\Support\Manager;

class TransactionManager extends Manager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']->get('simplesheet.transactions.handler');
    }

    /**
     * @return \Nikazooz\Simplesheet\Transactions\NullTransactionHandler
     */
    public function createNullDriver()
    {
        return new NullTransactionHandler();
    }

    /**
     * @return \Nikazooz\Simplesheet\Transactions\DbTransactionHandler
     */
    public function createDbDriver()
    {
        return new DbTransactionHandler(
            $this->app->get('db.connection')
        );
    }
}
