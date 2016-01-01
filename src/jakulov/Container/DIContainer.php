<?php
namespace jakulov\Container;

/**
 * Class DIContainer
 * @package jakulov\Container
 */
class DIContainer implements DependencyInjectionContainerInterface
{
    protected static $instance;
    /** @var array */
    protected $config = [];
    /** @var array */
    protected $services = [];
    /** @var array */
    protected $dependencyStack;
    /** @var array */
    protected $provideStack = [];

    /**
     * @param array $config
     * @return DIContainer
     */
    public static function getInstance(array $config = [])
    {
        if (self::$instance === null) {
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
        $this->config = $this->createFlatConfig($config);

        $this->services['service.di_container'] = &$this;
        $this->services['service.container'] = Container::getInstance($config);
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
     * @return Container
     */
    protected function getContainer()
    {
        return $this->services['service.container'];
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerException
     */
    public function get($id)
    {
        $id = $this->serviceAliasToId($id);
        $service = $this->getServiceConfigOrObject($id);
        if (is_object($service)) {
            return $service;
        }
        if (is_array($service)) {
            return $this->initService($id, $service);
        }
        if (is_string($service) && stripos($service, '@') === 0) {
            return $this->get($this->serviceAliasToId($service));
        }

        throw new ContainerException(sprintf('Service "%s" not found in config', $id));
    }

    /**
     * @param string $service
     * @return string
     */
    protected function serviceAliasToId($service)
    {
        return  stripos($service, 'service.') === 0 || stripos($service, 'service.') === 1 ?
            str_replace('@', '', $service) :
            'service.'. str_replace('@', '', $service);
    }

    /**
     * @param $id
     * @return mixed|null
     */
    protected function getServiceConfigOrObject($id)
    {
        $obj = isset($this->services[$id]) && is_object($this->services[$id]) ? $this->services[$id] : null;
        if ($obj === null) {
            $obj = $this->getServiceConfig($id);
        }

        return $obj;
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function getServiceConfig($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @param $id
     * @param array $serviceConfig
     * @param bool $asDependency
     * @return object
     * @throws ContainerException
     */
    protected function initService($id, array $serviceConfig, $asDependency = false)
    {
        if ($asDependency) {
            if (isset($this->dependencyStack[$id])) {
                throw new ContainerException(sprintf('Service'));
            }
        }
        if(isset($serviceConfig['parent']) && $serviceConfig['parent']) {
            $serviceConfig = $this->mergeWithParentConfig($serviceConfig);
        }

        $this->dependencyStack[$id] = 1;
        $service = $this->getServiceObject($id, $serviceConfig);

        $aware = isset($serviceConfig['aware']) ? $serviceConfig['aware'] : [];
        if (!is_array($aware)) {
            throw new ContainerException(sprintf('Service "%s" aware declaration should be an array'));
        }

        foreach (class_implements(get_class($service)) as $interface) {
            foreach ($this->getContainer()->get('container.di.aware.' . $interface, []) as $setter => $dependency) {
                $aware[$setter] = $dependency;
            }
        }

        $this->initServiceDependencies($id, $service, $aware);
        if ($asDependency === false) {
            $this->dependencyStack = [];
        }

        return $this->services[$id] = $service;
    }

    /**
     * @param string $id
     * @param object $dependency
     * @return $this
     */
    public function provide($id, $dependency)
    {
        $id = $this->serviceAliasToId($id);

        $this->services[$id] = $dependency;
        if(isset($this->provideStack[$id]) && is_array($this->provideStack[$id])) {
            foreach($this->provideStack[$id] as $serviceId => $setter) {
                $service = $this->services[$serviceId];
                call_user_func_array([$service, $setter], [$dependency]);
            }
        }

        return $this;
    }

    /**
     * @param $className
     * @param array $arguments
     * @return object
     */
    public function resolve($className, array $arguments = [])
    {
        $id = null;
        $service = $this->getServiceObject($id, [
            'class' => $className,
            'args' => $arguments,
        ]);

        $aware = [];
        foreach (class_implements(get_class($service)) as $interface) {
            foreach ($this->getContainer()->get('container.di.aware.' . $interface, []) as $setter => $dependency) {
                $aware[$setter] = $dependency;
            }
        }

        $this->initServiceDependencies($id, $service, $aware);
        $this->dependencyStack = [];

        return $service;
    }


    /**
     * @param array $config
     * @return array
     * @throws ContainerException
     */
    protected function mergeWithParentConfig(array $config)
    {
        $parentConfig = $this->getServiceConfig($this->serviceAliasToId($config['parent']));
        if($parentConfig === null) {
            throw new ContainerException(sprintf('Parent "%s" refs to unknown service', $config['parent']));
        }

        return array_replace_recursive($parentConfig, $config);
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
        if (class_exists($class)) {
            $arguments = isset($serviceConfig['args']) ? $serviceConfig['args'] : [];
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }
            if ($arguments) {
                $dependencies = [];
                foreach ($arguments as $dependency) {
                    $dependencies[] = $this->resolveDependency($id, $dependency, true);
                }
                $reflection = new \ReflectionClass($class);
                $service = $reflection->newInstanceArgs($dependencies);
            } else {
                $service = new $class;
            }

            return $service;
        }

        throw new ContainerException(sprintf('Unable to find class "%s" for service "%s"', $class, $id));
    }

    /**
     * @param $id
     * @param $dependency
     * @param bool|false $isArgs
     * @return mixed
     * @throws ContainerException
     */
    protected function resolveDependency($id, $dependency, $isArgs = false)
    {
        if (stripos($dependency, '@') === 0) {
            //$dependencyId = str_replace('@', '', $dependency);
            $dependencyId = $this->serviceAliasToId($dependency);
            $dependencyConfig = $this->getContainer()->get($dependencyId);
            if ($dependencyConfig && is_array($dependencyConfig)) {
                return $this->initService($dependencyId, $dependencyConfig, true);
            }
            else {
                throw new ContainerException(
                    sprintf('Service "%s" has dependency on not exists service "%s"', $id, $dependencyId)
                );
            }
        } elseif (stripos($dependency, ':') === 0) {
            $dependencyId = substr($dependency, 1);
            return $this->getContainer()->get($dependencyId);
        } else {
            return is_array($dependency) || $isArgs ? $dependency : [$dependency];
        }
    }

    /**
     * @param $id
     * @param $service
     * @param array $aware
     * @throws ContainerException
     */
    protected function initServiceDependencies($id, &$service, array $aware = [])
    {
        foreach ($aware as $setter => $dependency) {
            if (method_exists($service, $setter)) {
                call_user_func_array([$service, $setter], [$this->resolveDependency($id, $dependency, true)]);
                if(strpos($dependency, '@') === 0) {
                    // remember service depends on services
                    $this->provideStack[$this->serviceAliasToId($dependency)][$id] = $setter;
                }
            } else {
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