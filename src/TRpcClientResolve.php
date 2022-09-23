<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\ThriftRpc\Exception\TRpcException;
use Hyperf\ThriftRpc\Pool\TSocketConnection;
use Hyperf\ThriftRpc\Pool\TSocketPoolFactory;
use Hyperf\Utils\Context;

class TRpcClientResolve
{
    protected $contextKeyPre = 'thrift.connection.';

    /**
     * @var TSocketPoolFactory
     */
    protected $socketPool;

    /**
     * @var TSocketConnection
     */
    private $connection;
    /**
     * @var bool
     */
    private $async;
    /**
     * @var mixed
     */
    private $client;

    public function __construct(TSocketPoolFactory $socketPool)
    {
        $this->socketPool = $socketPool;
    }

    public function get($poolName, bool $async = false)
    {
        $this->async = $async;
        $this->connection = $this->getConnection($poolName);
        $transport = Utils::getFramedTransport($this->connection);
        $protocol = Utils::getBinaryProtocol($transport);
        $transport->open();
        $clientClass = "\\App\\Services\\" . $poolName . "\\" . $poolName . 'Client';
        $this->client = make($clientClass, ['input' => $protocol, 'output' => $protocol]);
        return $this;
    }

    public function __call($name, $arguments)
    {
        if (!$this->client) throw new TRpcException('Client does not declare');
        try {
            if ($this->async) $name = 'send_' . $name;
            $res = $this->client->{$name}(...$arguments);
        } finally {
            $this->connection->release();
        }
        return $res;
    }

    protected function getConnection($name)
    {
        $connection = null;
        $contextKey = $this->contextKeyPre . $name;
        $hasConnection = Context::has($contextKey);
        if ($hasConnection) {
            $connection = Context::get($contextKey);
        }
        if (!$connection instanceof TSocketConnection) {
            $pool = $this->socketPool->getPool($name);
            $connection = $pool->get();
        }
        if (!$connection instanceof TSocketConnection) {
            throw new TRpcException('The connection is not a valid TSocketConnection.');
        }
        if (!$hasConnection) Context::set($contextKey, $connection);
        return $connection;
    }
}