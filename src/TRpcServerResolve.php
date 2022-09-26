<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnReceiveInterface;
use Hyperf\ThriftRpc\Exception\TRpcException;
use Hyperf\Utils\ApplicationContext;

class TRpcServerResolve implements OnReceiveInterface, OnCloseInterface
{

    /**
     * @var string
     */
    private $serverName;
    /**
     * @var mixed
     */
    private $processor;

    public function onConnect($server, int $fd, int $reactorId): void
    {
        $connectionInfo = $server->connection_info($fd);
        $serverPort = $connectionInfo['server_port'];
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $servers = $config->get('server.servers');
        $serverName = '';
        if (!empty($servers)) {
            foreach ($servers as $s) {
                if ($s['port'] === $serverPort) {
                    $serverName = $s['handler'] ?? $s['name'];
                    break;
                }
            }
        }
        if (empty($serverName)) {
            $server->close($fd);
            throw new TRpcException("Service not found");
        }
        $configs = $config->get('thrift');
        if (empty($configs[$serverName])) {
            $server->close($fd);
            throw new TRpcException("Service config not found");
        }
        $this->serverName = $serverName;
        $processorClass = "\\App\\Services\\" . $serverName . "\\" . $serverName . 'Processor';
        if (!class_exists($processorClass)) {
            $server->close($fd);
            throw new TRpcException("Class {$processorClass} not found");
        }

        $handlerClass = "\\App\\Services\\" . $serverName . 'Handler';
        if (!class_exists($handlerClass)) {
            $server->close($fd);
            throw new TRpcException("Class {$handlerClass} not found");
        }
        try {
            $handler = new $handlerClass();
            $this->processor = new $processorClass($handler);
        } catch (\Throwable $throwable) {
            $server->close($fd);
            throw $throwable;
        }
    }

    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        try {
            $socket = new TSocketTransport();
            $socket->setHandle($fd);
            $socket->buffer = $data;
            $socket->server = $server;
            $protocol = new \Thrift\Protocol\TBinaryProtocol($socket, true, true);
            $this->processor->process($protocol, $protocol);
        } catch (\Throwable $throwable) {
            $server->close($fd);
            throw $throwable;
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $server->close($fd);
        $this->serverName = null;
        $this->processor = null;
    }
}