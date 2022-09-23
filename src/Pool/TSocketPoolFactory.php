<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc\Pool;

use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;

class TSocketPoolFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TSocketPool[]
     */
    protected $pools = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name): TSocketPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        if ($this->container instanceof Container) {
            $pool = $this->container->make(TSocketPool::class, ['name' => $name]);
        } else {
            $pool = new TSocketPool($this->container, $name);
        }
        return $this->pools[$name] = $pool;
    }
}