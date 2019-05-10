<?php

namespace Nikazooz\Simplesheet\Transactions;

use Illuminate\Database\ConnectionInterface;

class DbTransactionHandler implements TransactionHandler
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @return void
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param  callable  $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    public function __invoke(callable $callback)
    {
        return $this->connection->transaction($callback);
    }
}
