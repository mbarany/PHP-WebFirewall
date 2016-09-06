<?php

namespace Barany\WebFirewall;

class JsonStore implements DataStoreInterface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->readFromDisk();
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        $this->writeToDisk();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = NULL)
    {
        if (!isset($this->data[$key])) {
            return $default;
        }
        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        unset($this->data[$key]);
        $this->writeToDisk();
        return $this;
    }

    private function writeToDisk()
    {
        file_put_contents($this->file, json_encode($this->data));
    }

    private function readFromDisk()
    {
        if (!file_exists($this->file)) {
            $this->writeToDisk();
            return;
        }
        $this->data = json_decode(file_get_contents($this->file), true);
    }
}
