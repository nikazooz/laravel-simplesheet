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
        return $this->getContainer()['config']->get('simplesheet.transactions.handler');
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
            $this->getContainer()->get('db.connection')
        );
    }

    protected function getContainer()
    {
        if (isset($this->container)) {
            return $this->container;
        }

        return $this->app;
    }
}
