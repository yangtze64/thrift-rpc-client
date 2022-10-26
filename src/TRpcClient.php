<?php
/** @noinspection ALL */
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

class TRpcClient
{
    private $client;

    public function __construct($name)
    {
        $this->client = make(TRpcClientResolve::class)->get($name);
    }

    public function __call($name, $arguments)
    {
        $async = false;
        if (0 === strpos($name, 'async')) {
            $async = true;
            $name = substr($name, 5);
        }
        return $this->client->_async($async)->{$name}(...$arguments);
    }
}
