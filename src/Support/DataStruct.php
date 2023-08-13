<?php

namespace Iyuu\Spider\Support;

use ArrayAccess;
use ArrayIterator;
use think\contract\Arrayable;
use think\contract\Jsonable;
use Traversable;

/**
 * 数据结构基础类
 */
class DataStruct implements ArrayAccess, Arrayable, Jsonable
{
    /**
     * 数据
     * @var array
     */
    protected array $data = [];

    /**
     * 构造函数
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * 转数组
     * @return array
     */
    final public function toArray(): array
    {
        return $this->data;
    }

    /**
     * 获取
     * @return array
     */
    final public function getData(): array
    {
        return $this->data;
    }

    /**
     * 设置
     * @param array $data
     * @return DataStruct
     */
    final public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 当对不可访问属性调用 isset() 或 empty() 时，__isset() 会被调用
     *
     * @param int|string $name
     * @return bool
     */
    final public function __isset(int|string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * 当对不可访问属性调用 unset() 时，__unset() 会被调用
     *
     * @param int|string $name
     */
    final public function __unset(int|string $name)
    {
        unset($this->data[$name]);
    }

    /**
     * 当访问不可访问属性时调用
     * @param string $name
     * @return array|string|null
     */
    final public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 在给不可访问（protected 或 private）或不存在的属性赋值时，__set() 会被调用。
     * @param int|string $key
     * @param mixed $value
     */
    final public function __set(int|string $key, mixed $value)
    {
        $this->set($key, $value);
    }

    /**
     * 获取配置项参数【支持 . 分割符】
     * @param int|string|null $key
     * @param null $default
     * @return mixed
     */
    final public function get(int|string $key = null, $default = null): mixed
    {
        if (null === $key) {
            return $this->data;
        }
        $keys = explode('.', $key);
        $value = $this->data;
        foreach ($keys as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }

    /**
     * 设置 $this->data
     * @param int|string|null $key
     * @param mixed $value
     * @return DataStruct
     */
    final public function set(int|string|null $key, mixed $value): DataStruct
    {
        if ($key === null) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    // ArrayAccess
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    //Countable
    public function count(): int
    {
        return count($this->data);
    }

    //IteratorAggregate
    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * 转换当前数据集为JSON字符串
     * @access public
     * @param integer $options json参数
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
