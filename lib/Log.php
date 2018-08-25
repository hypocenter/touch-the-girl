<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/25
 * Time: 23:57
 */

namespace Ttg;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Log
 * @mixin Logger
 */
class Log extends Component
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @throws \Exception
     */
    public function boot()
    {
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler(
            $this->container->config->get('log.path'),
            $this->container->config->get('log.level'))
        );
    }

    public function __call($name, $arguments)
    {
        $this->logger->$name(...$arguments);
    }
}