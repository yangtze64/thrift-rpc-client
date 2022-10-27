<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\Di\Container;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class TSimpleRpcClientFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TSocket
     */
    protected $connection;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get($name): TSimpleRpcClient
    {
        if ($this->container instanceof Container) {
            $client = $this->container->make(TSimpleRpcClient::class, ['name' => $name]);
        } else {
            $client = new TSimpleRpcClient($this->container, $name);
        }
        return $client;
    }

}