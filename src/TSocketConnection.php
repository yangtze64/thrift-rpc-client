<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TFramedTransport;

class TSocketConnection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var TSocket
     */
    protected $connection;

    /**
     * @var TFramedTransport
     */
    protected $transport;

    /**
     * @var TBinaryProtocol
     */
    public $protocol;

    /**
     * @var array
     */
    protected $config = [
        'host' => 'localhost',
        'port' => 9301,
        'timeout' => 0.0,
    ];

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);

        $this->config = array_replace($this->config, $config);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function reconnect(): bool
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $timeout = $this->config['timeout'];
        $this->connection = new TSocket($host, $port, $timeout);
        $this->transport = Utils::getFramedTransport($this->connection);
        $this->protocol = Utils::getBinaryProtocol($this->transport);
        $this->transport->open();
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function check(): bool
    {
        if (!parent::check()) return false;
        return $this->connection && $this->connection->isOpen();
    }

    public function close(): bool
    {
        $this->connection && $this->connection->close();
        $this->connection = null;
        return true;
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }
        $this->close();
        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }
        return $this;
    }
}
