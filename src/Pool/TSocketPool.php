<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;

class TSocketPool extends Pool
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('thrift.%s', $this->name);
        if (!$config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);

        $this->frequency = make(Frequency::class);

        parent::__construct($container, $options);
    }

    public function createConnection(): TSocketConnection
    {
        return new TSocketConnection($this->container, $this, $this->config);
    }
}