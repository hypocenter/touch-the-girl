<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/25
 * Time: 23:28
 */

namespace Ttg;


trait ContainerAwareTrait
{
    /**
     * @var Container
     */
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}