<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/25
 * Time: 23:23
 */

namespace Ttg;


/**
 * @property-read Log $log
 * @property-read Config $config
 * @property-read Accounts $accounts
 */
class Container
{
    private $instances = [];

    public function __construct()
    {
        $this->instances['config'] = $this->make(Config::class, realpath(__DIR__ . '/../config'));
        $this->instances['log'] = $this->make(Log::class);
        $this->instances['accounts'] = $this->make(Accounts::class);

        foreach ($this->instances as $instance) {
            if ($instance instanceof Bootable) {
                $instance->boot();
            }
        }
    }

    public function get($name)
    {
        return $this->instances[$name];
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function make($cls, ...$args)
    {
        $instance = new $cls(...$args);
        if ($instance instanceof ContainerAware) {
            $instance->setContainer($this);
        }
        return $instance;
    }
}