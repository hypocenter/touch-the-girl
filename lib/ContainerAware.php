<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/25
 * Time: 23:30
 */

namespace Ttg;


interface ContainerAware
{
    public function setContainer(Container $container);
}