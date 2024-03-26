<?php

namespace Iyuu\Spider;

/**
 * 容器
 */
class Container extends \think\Container implements \Ledc\Pipeline\Container
{
    /**
     * 容器对象实例
     * @var \think\Container|Closure
     */
    protected static $instance;
}
