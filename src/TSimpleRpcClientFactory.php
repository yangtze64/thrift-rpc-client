<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;

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

    /**
     * 获取简单的客户端实例
     * @param $name
     * @return TSimpleRpcClient
     * @throws Exception\TRpcException
     * @throws \Hyperf\Di\Exception\NotFoundException
     */
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