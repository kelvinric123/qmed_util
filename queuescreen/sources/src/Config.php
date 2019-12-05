<?php

namespace Rasque;

class Config
{
    protected $path;

    protected $config = [];

    /** @var Config $instance */
    protected static $instance;

    protected function __construct()
    {
        $this->path = __DIR__ . '/../../config.json';

        if (file_exists($this->path))
            $this->config = json_decode(file_get_contents($this->path), true);
    }

    /**
     * @return Config
     */
    public static function instance()
    {
        if (!static::$instance)
            static::$instance = new static();

        return static::$instance;
    }

    public function get($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->config[$key] = $value;

        file_put_contents($this->path, json_encode($this->config, JSON_PRETTY_PRINT));

        return $this;
    }
}