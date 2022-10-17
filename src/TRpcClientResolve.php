<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\Context\Context;
use Hyperf\ThriftRpc\Exception\TRpcException;
use Hyperf\ThriftRpc\Pool\TSocketPoolFactory;

class TRpcClientResolve
{
    protected $contextKeyPre = 'thrift.connection.';

    /**
     * @var TSocketPoolFactory
     */
    protected $socketPool;
    /**
     * @var bool
     */
    private $async;

    /**
     * @var string
     */
    private $poolName;

    public function __construct(TSocketPoolFactory $socketPool)
    {
        $this->socketPool = $socketPool;
    }

    public function get($poolName, bool $async = false)
    {
        $this->poolName = $poolName;
        $this->async = $async;
        return $this;
    }

    public function _async(bool $async = true)
    {
        $this->async = $async;
        return $this;
    }

    public function __call($name, $arguments)
    {
        $res = null;
        $contextKey = $this->contextKeyPre . $name;
        $connection = $this->getConnection($contextKey);
        try {
            $connection = $connection->getConnection();
            $clientClass = "\\App\\Services\\" . $this->poolName . "\\" . $this->poolName . 'Client';
            $client = make($clientClass, ['input' => $connection->protocol, 'output' => $connection->protocol]);
            if ($this->async) {
                $client->{'send_' . $name}(...$arguments);
//                $client->{'recv_' . $name}();
                $connection->close();
            } else {
                $res = $client->{$name}(...$arguments);
            }
        } finally {
            if (Context::has($contextKey)) Context::set($contextKey, null);
            $connection->release();
        }
        return $res;
    }

    public function getConnection($contextKey)
    {
        $connection = null;
        $hasConnection = Context::has($contextKey);
        if ($hasConnection) {
            $connection = Context::get($contextKey);
        }
        if (!$connection instanceof TSocketConnection) {
            $pool = $this->socketPool->getPool($this->poolName);
            $connection = $pool->get();
        }
        if (!$connection instanceof TSocketConnection) {
            throw new TRpcException('The connection is not a valid TSocketConnection.');
        }
        return $connection;
    }
}