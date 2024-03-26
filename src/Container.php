<?php

namespace Iyuu\Spider;

use Closure;

/**
 * 容器
 */
class Container extends \think\Container implements \Ledc\Pipeline\Container
{
    /**
     * 容器对象实例
     * @var Container|\Ledc\Pipeline\Container|Closure
     */
    protected static $instance;
}
