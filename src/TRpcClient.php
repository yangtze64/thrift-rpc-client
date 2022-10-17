<?php
/** @noinspection ALL */
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

class TRpcClient
{
    private static $instance;
    private static $_async = false;

    static function getInstance(...$args)
    {
        self::$_async = false;
        if (!isset(self::$instance)) {
//            self::$instance = new static(...$args);
            self::$instance = make(static::class, $args);
        }
        return self::$instance;
    }

    private $client;


    public function __construct($name)
    {
        $this->client = make(TRpcClientResolve::class)->get($name);
    }

    public static function async($name)
    {
        $instance = self::getInstance($name);
        self::$_async = true;
        return $instance;
    }

    public function __call($name, $arguments)
    {
        return $this->client->_async(self::$_async)->{$name}(...$arguments);
    }

}
