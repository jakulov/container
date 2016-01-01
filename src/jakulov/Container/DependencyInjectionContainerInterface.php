<?php
namespace jakulov\Container;

use Interop\Container\ContainerInterface;

/**
 * Class DIContainer
 * @package jakulov\Container
 */
interface DependencyInjectionContainerInterface extends ContainerInterface
{
    /**
     * Provide new or changed object to it's dependent services (e.g. Request instance)
     *
     * @param string $id
     * @param object $dependency
     * @return $this
     */
    public function provide($id, $dependency);

    /**
     * Create object class and resolve it's dependencies though aware interfaces
     *
     * @param string $className
     * @param array $arguments
     * @return object
     */
    public function resolve($className, array $arguments = []);
}