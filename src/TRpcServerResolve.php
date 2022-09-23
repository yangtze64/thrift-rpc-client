<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnReceiveInterface;

class TRpcServerResolve implements OnReceiveInterface, OnCloseInterface
{

    public function onConnect($server, int $fd, int $reactorId)
    {

    }

    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {

    }

    public function onClose($server, int $fd, int $reactorId)
    {

    }
}