<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/26
 * Time: 00:01
 */

namespace Ttg;


class Component implements Bootable, ContainerAware
{
    use ContainerAwareTrait;

    public function boot() {}
}