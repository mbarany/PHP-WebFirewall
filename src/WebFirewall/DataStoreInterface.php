<?php

namespace Barany\WebFirewall;

interface DataStoreInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = NULL);

    /**
     * @param string $key
     * @return self
     */
    public function delete($key);
}
