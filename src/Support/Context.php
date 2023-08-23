<?php

namespace Iyuu\Spider\Support;

use SplObjectStorage;
use StdClass;
use WeakMap;
use function property_exists;

/**
 * 运行时上下文
 * - 代码来自Webman
 */
class Context
{
    /**
     * @var null|SplObjectStorage|WeakMap
     */
    protected static null|WeakMap|SplObjectStorage $objectStorage = null;

    /**
     * @var ?StdClass
     */
    protected static null|StdClass $object = null;

    /**
     * @param string|null $key
     * @return mixed
     */
    public static function get(string $key = null): mixed
    {
        $obj = static::getObject();
        if ($key === null) {
            return $obj;
        }
        return $obj->$key ?? null;
    }

    /**
     * @return StdClass
     */
    protected static function getObject(): StdClass
    {
        if (!static::$objectStorage) {
            static::$objectStorage = class_exists(WeakMap::class) ? new WeakMap() : new SplObjectStorage();
            static::$object = new StdClass;
        }
        $key = static::getKey();
        if (!isset(static::$objectStorage[$key])) {
            static::$objectStorage[$key] = new StdClass;
        }
        return static::$objectStorage[$key];
    }

    /**
     * @return StdClass|null
     */
    protected static function getKey(): ?StdClass
    {
        return static::$object;
    }

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        $obj = static::getObject();
        $obj->$key = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public static function delete(string $key): void
    {
        $obj = static::getObject();
        unset($obj->$key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        $obj = static::getObject();
        return property_exists($obj, $key);
    }

    /**
     * @return void
     */
    public static function destroy(): void
    {
        unset(static::$objectStorage[static::getKey()]);
    }
}
