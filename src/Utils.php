<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TFramedTransport;

class Utils
{
    public static function getFramedTransport($socket, $read = true, $write = true)
    {
        return make(TFramedTransport::class, ['transport' => $socket, 'read' => $read, 'write' => $write]);
    }

    public static function getBinaryProtocol($transport, $strictRead = false, $strictWrite = true)
    {
        return make(TBinaryProtocol::class, ['trans' => $transport, 'strictRead' => $strictRead, 'strictWrite' => $strictWrite]);
    }
}