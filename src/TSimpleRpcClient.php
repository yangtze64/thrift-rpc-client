<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ThriftRpc\Exception\TRpcException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class TSimpleRpcClient
{
    /**
     * @var string
     */
    protected $prekey = 'thrift.';
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TSocket
     */
    protected $connection;

    public function __construct(ContainerInterface $container, string $name)
    {
        $config = $container->get(ConfigInterface::class);
        if (empty($name) || !$config->has($this->prekey . $name)) {
            throw new TRpcException(sprintf('No service config named `%s` was found', $name ?: null));
        }
        $this->name = $name;
        $this->config = $config->get($this->prekey . $this->name);
        $this->logger = $container->get(LoggerFactory::class)->get('rpc.client');
        $this->container = $container;
    }

    protected function _client()
    {
        $this->connection = new TSocket($this->config['host'], $this->config['port'], $this->config['timeout']);
        $transport = Utils::getFramedTransport($this->connection);
        $protocol = Utils::getBinaryProtocol($transport);
        $transport->open();
        $clientClass = "\\App\\Services\\" . $this->name . "\\" . $this->name . 'Client';
        return $this->container->make($clientClass, ['input' => $protocol, 'output' => $protocol]);
    }

    public function __call($method, $args)
    {
        $result = null;
        $client = null;
        $async = false;
        if (0 === strpos($method, 'async')) {
            $async = true;
            $method = substr($method, 5);
        }
        try {
            $client = $this->_client();
            if ($async) {
                $client->{'send_' . $method}(...$args);
            } else {
                $result = $client->{$method}(...$args);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
            throw new TRpcException('RPC service exception,err:' . $e->getMessage());
        } finally {
            if ($client) $client = null;
            if ($this->connection) {
                $this->connection->close();
                $this->connection = null;
            }
        }
        return $result;
    }
}