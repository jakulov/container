<?php
namespace jakulov\Container;

/**
 * Class DIContainer
 * @package jakulov\Container
 */
class DIContainer
{
    protected static $instance;
    /** @var array */
    protected $config = [];

    /**
     * @param array $config
     * @return DIContainer
     */
    public static function getInstance(array $config = [])
    {
        if(self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * DIContainer constructor.
     * @param array $config
     */
    protected function __construct(array $config)
    {
        $this->createFlatConfig($config);

        $this->config['service.di_container'] = &$this;
        $this->config['service.container'] = Container::getInstance($config);
    }

    /**
     * @param array $config
     * @param string $key
     * @param array $flatConfig
     * @return array
     */
    protected function createFlatConfig(array $config, $key = '', &$flatConfig = []) : array
    {
        foreach($config as $k => $value) {
            $index = ($key ? $key .'.'. $k : $k);
            $flatConfig[$index] = $value;
            if(is_array($value)) {
                $this->createFlatConfig($value, $index, $flatConfig);
            }
        }

        return $flatConfig;
    }

    private function __clone()
    {

    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->config['service.container'];
    }

    /**
     * @param string $id
     * @return mixed|object
     * @throws ContainerException
     */
    public function get($id)
    {
        $service = $this->getServiceConfigOrObject($id);
        if(is_object($service)) {
            return $service;
        }
        if(is_array($service)) {
            return $this->initService($id, $service);
        }

        throw new ContainerException(sprintf('Service "%s" not found in config', $id));
    }

    /**
     * @param $id
     * @return mixed|null
     */
    protected function getServiceConfigOrObject($id)
    {
        $obj = isset($this->config[$id]) && is_object($this->config[$id]) ? $this->config[$id] : null;
        if(!$obj) {
            $obj = $this->getContainer()->get($id);
        }

        return $obj;
    }

    /** @var array */
    protected $dependencyStack;

    /**
     * @param $id
     * @param array $serviceConfig
     * @param bool $asDependency
     * @return object
     * @throws ContainerException
     */
    protected function initService($id, array $serviceConfig, $asDependency = false)
    {
        if($asDependency) {
            if(isset($this->dependencyStack[$id])) {
                throw new ContainerException(sprintf('Service'));
            }
        }
        $this->dependencyStack[$id] = 1;
        $service = $this->getServiceObject($id, $serviceConfig);

        $aware = isset($serviceConfig['aware']) ? $serviceConfig['aware'] : [];
        if(!is_array($aware)) {
            throw new ContainerException(sprintf('Service "%s" aware declaration should be an array'));
        }

        foreach(class_implements(get_class($service)) as $interface) {
            foreach($this->getContainer()->get('container.di.aware.' . $interface, []) as $setter => $dependency) {
                $aware[$setter] = $dependency;
            }
        }

        $this->initServiceDependencies($id, $service, $aware);
        if($asDependency === false) {
            $this->dependencyStack = [];
        }

        return $this->config[$id] = $service;
    }

    /**
     * @param $id
     * @param array $serviceConfig
     * @return object
     * @throws ContainerException
     */
    protected function getServiceObject($id, array $serviceConfig = [])
    {
        $class = isset($serviceConfig['class']) ? $serviceConfig['class'] : '';
        if(class_exists($class)) {
            $arguments = isset($serviceConfig['args']) ? $serviceConfig['args'] : [];
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }
            if ($arguments) {
                $reflection = new \ReflectionClass($class);
                $service = $reflection->newInstanceArgs($arguments);
            } else {
                $service = new $class;
            }

            return $service;
        }

        throw new ContainerException(sprintf('Unable to find class "%s" for service "%s"', $class, $id));
    }

    /**
     * @param $id
     * @param $service
     * @param array $aware
     * @throws ContainerException
     */
    protected function initServiceDependencies($id, &$service, array $aware = [])
    {
        foreach($aware as $setter => $dependency) {
            if(method_exists($service, $setter)) {
                if(stripos($dependency, '@') === 0) {
                    $dependencyId = str_replace('@', '', $dependency);
                    $dependencyConfig = $this->getContainer()->get($dependencyId);
                    if($dependencyConfig) {
                        call_user_func_array([$service, $setter], [
                            $this->initService($dependencyId, $dependencyConfig, true)
                        ]);
                    }
                    else {
                        throw new ContainerException(
                            sprintf('Service "%s" has dependency on not exists service "%s"', $id, $dependencyId)
                        );
                    }
                }
                elseif(stripos($dependency, ':') === 0) {
                    $dependencyId = substr($dependency, 1);
                    call_user_func_array([$service, $setter], [
                        $this->getContainer()->get($dependencyId)
                    ]);
                }
                else {
                    call_user_func_array([$service, $setter], is_array($dependency) ? $dependency : [$dependency]);
                }
            }
            else {
                throw new ContainerException(
                    sprintf('Method "%s" not exists in class "%s"', $setter, get_class($service))
                );
            }
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->config[$id]);
    }
}