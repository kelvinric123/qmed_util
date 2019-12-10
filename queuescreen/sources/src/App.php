<?php

namespace Rasque;

class App
{
    protected static $instance;

    public static function instance()
    {
        if (!static::$instance)
            static::$instance = new static();

        return static::$instance;
    }

    public function getBasePath()
    {
        return realpath(__DIR__ . '/../..');
    }
}