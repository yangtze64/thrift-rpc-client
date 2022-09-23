<?php
declare(strict_types=1);

namespace Hyperf\ThriftRpc\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class ThriftCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gen:thrift');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate thrift server and client files');
        $this->setHelp('参数names用来生成ThriftRpc文件');
        $this->addArgument('names', InputArgument::IS_ARRAY, '*.thrift 名');
    }

    public function handle()
    {
        $args = $this->input->getArgument('names');
        if (!empty($args)) {
            $this->line('Generate thrift files ...', 'info');
            $thrift_file = [];
            $fileDir = BASE_PATH . '/IDL/';
            if (count($args) === 1 && $args[0] === '*') {
                foreach (glob($fileDir . '*.thrift') as $file) {
                    $thrift_file[] = $file;
                }
            } else {
                foreach ($args as $val) {
                    $filename = mb_strpos($val, '.thrift') !== false ? $val : $val . '.thrift';
                    if (file_exists($file = $fileDir . $filename)) {
                        $thrift_file[] = $file;
                    } else {
                        $this->line($val . ' thrift file does not exist', 'info');
                    }
                }
            }
            if (!empty($thrift_file)) {
                foreach ($thrift_file as $path) {
                    exec('thrift --gen php:server -o ' . $fileDir . ' ' . $path);
                    $this->line($path . ' thrift files generated success', 'info');
                }
                $this->line('move thrift service files ...', 'info');
                $oldServicesPath = $fileDir . 'gen-php/App/Services/*';
                $servicesPath = BASE_PATH . '/app/Services/';
                exec('cp -Rf ' . $oldServicesPath . ' ' . $servicesPath);
                $this->line('move thrift service files success', 'info');
                exec('rm -rf ' . $fileDir . 'gen-php');
                $this->line('rm thrift old files success', 'info');
            }
        } else {
            $this->line('files not generated', 'info');
        }
    }
}
