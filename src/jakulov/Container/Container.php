<?php
namespace jakulov\Container;

use Interop\Container\ContainerInterface;

/**
 * Class Container
 * @package jakulov\Container
 */
class Container implements ContainerInterface
{
    protected static $instance;
    /** @var array */
    protected $config = [];

    /**
     * @param array $config
     * @return Container
     */
    public static function getInstance(array $config = [])
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Container constructor.
     * @param array $config
     */
    protected function __construct(array $config)
    {
        $this->config = $this->createFlatConfig($config);
    }

    /**
     * @param array $config
     * @param string $key
     * @param array $flatConfig
     * @return array
     */
    protected function createFlatConfig(array $config, $key = '', &$flatConfig = []) : array
    {
        foreach ($config as $k => $value) {
            $index = ($key ? $key . '.' . $k : $k);
            $flatConfig[$index] = $value;
            if (is_array($value)) {
                $this->createFlatConfig($value, $index, $flatConfig);
            }
        }

        return $flatConfig;
    }


    private function __clone()
    {

    }

    /**
     * @param string $id
     * @param null $default
     * @return mixed
     */
    public function get($id, $default = null)
    {
        return isset($this->config[$id]) ?
            $this->config[$id] :
            $default;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->config[$id]);
    }

}