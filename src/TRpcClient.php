<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

class TRpcClient
{
    private static $instance;

    static function getInstance(...$args)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }

    private $client;

    public function __construct($name)
    {
        $this->client = make(TRpcClientResolve::class)->get($name);
    }

    public function async()
    {
        return $this->client->_async(true);
    }

    public function __call($name, $arguments)
    {
        return $this->client->_async(false)->{$name}(...$arguments);
    }

}
