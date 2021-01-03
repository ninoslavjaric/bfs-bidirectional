<?php
namespace Htec\Traits;

trait SingletonTrait
{
    private static array $instances = [];

    static public function getInstance(): self
    {
        if (!array_key_exists(static::class, static::$instances)) {
            static::$instances[static::class] = new static();
        }

        return static::$instances[static::class];
    }
}
