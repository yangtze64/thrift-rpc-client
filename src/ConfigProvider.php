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
namespace Hyperf\ThriftRpc;

use Hyperf\ThriftRpc\Command\ThriftCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [

            ],
            'commands' => [
                ThriftCommand::class
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of thrift client.',
                    'source' => __DIR__ . '/../publish/thrift.php',
                    'destination' => BASE_PATH . '/config/autoload/thrift.php',
                ],
                [
                    'id' => 'thrift',
                    'description' => 'thrift IDL.',
                    'source' => __DIR__ . '/../publish/User.thrift',
                    'destination' => BASE_PATH . '/IDL/User.thrift',
                ],
                [
                    'id' => 'rpc-handler',
                    'description' => 'thrift IDL.',
                    'source' => __DIR__ . '/../publish/UserHandler.php',
                    'destination' => BASE_PATH . '/app/Services/UserHandler.php',
                ],
            ],
        ];
    }
}
