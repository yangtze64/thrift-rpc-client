<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'User' => [
        'host' => env('USER_THRIFT_HOST', '0.0.0.0'),
        'port' => (int)env('USER_THRIFT_PORT', 9301),
        'timeout' => 0.0,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60,
        ],
    ],
];

// 创建服务端可用以下参考配置
// server 参考配置
//'servers' => [
//    // ...
//    [
//        'name' => 'User',
//        'type' => Server::SERVER_BASE,
//        'host' => env('USER_THRIFT_HOST', '0.0.0.0'),
//        'port' => (int) env('USER_THRIFT_PORT', 9301),
//        'sock_type' => SWOOLE_SOCK_TCP,
//        'callbacks' => [
//            SwooleEvent::ON_CONNECT => ['UserTRpcServer', 'onConnect'],
//            SwooleEvent::ON_RECEIVE => ['UserTRpcServer', 'onReceive'],
//            SwooleEvent::ON_CLOSE => ['UserTRpcServer', 'onClose'],
//        ],
//        'settings' => [
//            'open_length_check' => true,
//            'package_length_type' => 'N',
//            'package_length_offset' => 0,
//            'package_body_offset' => 4,
//            'package_max_length' => 1024 * 1024 * 8,
//        ]
//    ]
//    // ...
//],

// dependencies 参考配置
//return [
//    // ...
//    'UserTRpcServer' => \Hyperf\ThriftRpc\TRpcServerResolve::class,
//    // ...
//];
