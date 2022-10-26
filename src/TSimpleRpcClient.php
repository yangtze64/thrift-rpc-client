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
     * @var TSocket
     */
    protected $connection;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(LoggerFactory::class)->get('rpc.client');
    }

    public function get($name): TSimpleRpcClient
    {
        if (empty($name) || !$this->config->has($name)) {
            throw new \Exception(sprintf('No service config named `%s` was found', $name ?: null));
        }
        $this->name = $name;
        return $this;
    }

    protected function _client()
    {
        $config = $this->_rpcConfig();
        $this->connection = new TSocket($config['host'], $config['port'], $config['timeout']);
        $transport = Utils::getFramedTransport($this->connection);
        $protocol = Utils::getBinaryProtocol($transport);
        $transport->open();
        $clientClass = "\\App\\Services\\" . $this->name . "\\" . $this->name . 'Client';
        return make($clientClass, ['input' => $protocol, 'output' => $protocol]);
    }

    protected function _rpcConfig()
    {
        return $this->config->get('thrift.' . $this->name);
    }

    public function __call($method, $args)
    {
        if (empty($this->name)) throw new TRpcException('No service found, please GET service first');
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
            if ($this->connection){
                $this->connection->close();
                $this->connection = null;
            }
        }
        return $result;
    }
}