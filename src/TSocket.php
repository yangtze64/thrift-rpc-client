<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc;

use Thrift\Exception\TTransportException;
use Thrift\Transport\TTransport;

class TSocket extends TTransport
{

    /**
     * Remote hostname
     *
     * @var string
     */
    protected $host_ = 'localhost';

    /**
     * Remote port
     *
     * @var int
     */
    protected $port_ = 9090;

    /**
     * @var float
     */
    protected $timeout_ = 0.0;
    /**
     * @var \Swoole\Client
     */
    private $client_;

    public function __construct(string $host = 'localhost', int $port = 9090, float $timeout = 0.0)
    {
        $this->host_ = $host;
        $this->port_ = $port;
        $this->timeout_ = $timeout;
    }

    public function getClient()
    {
        return $this->isOpen() ? $this->client_ : false;
    }

    public function isOpen()
    {
        return $this->client_ && $this->client_->isConnected();
    }

    public function open()
    {
        if ($this->isOpen()) {
            throw new TTransportException('Socket already connected', TTransportException::ALREADY_OPEN);
        }
        if (empty($this->host_)) {
            throw new TTransportException('Cannot open null host', TTransportException::NOT_OPEN);
        }

        if ($this->port_ <= 0) {
            throw new TTransportException('Cannot open without port', TTransportException::NOT_OPEN);
        }
        $client = new \Swoole\Client(SWOOLE_SOCK_TCP);
        if (!$client->connect($this->host_, $this->port_, $this->timeout_ <= 0 ? -1 : $this->timeout_)) {
            unset($client);
            throw new TTransportException('Socket connect failed', TTransportException::NOT_OPEN);
        }
        $this->client_ = $client;
    }

    public function close()
    {
        $this->isOpen() && $this->client_->close(true);
        $this->client_ = null;
    }

    public function read($len)
    {
        if (!$this->isOpen()) {
            $this->close();
            throw new TTransportException('Socket: read error ' . $this->host_ . ':' . $this->port_, TTransportException::NOT_OPEN);
        }
        /**
         * 成功收到数据返回字符串
         * 连接关闭返回空字符串
         * 失败返回 false，并设置 $client->errCode 属性
         */
        $data = $this->client_->recv($len);
        if (empty($data)) {
            $this->close();
            $errCode = $data === false ? $this->client_->errCode : 0;
            throw new TTransportException('Socket: Could not read ' . $len . ' bytes from ' . $this->host_ . ':' . $this->port_, $errCode);
        }
        return $data;
    }

//    public function readAll($len)
//    {
//        return $this->read($len);
//    }

    public function write($buf)
    {
        if (!$this->isOpen()) {
            $this->close();
            throw new TTransportException('Socket: write error ' . $this->host_ . ':' . $this->port_, TTransportException::NOT_OPEN);
        }
        $this->client_->send((string)$buf);
    }

    public function flush()
    {

    }
}