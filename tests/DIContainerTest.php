<?php

require __DIR__ .'/Service/InterfaceTestServiceAwareInterface.php';
require __DIR__ .'/Service/InterfaceTestService.php';
require __DIR__ .'/Service/AnotherTestService.php';
require __DIR__ .'/Service/TestService.php';


class DIContainerTest extends PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $config = require __DIR__ .'/../src/jakulov/Container/config.php';
        $dic = \jakulov\Container\DIContainer::getInstance($config);

        /** @var Service\TestService $testService */
        $testService = $dic->get('service.test');
        $this->assertEquals(Service\TestService::class, get_class($testService));
        $this->assertEquals('argument1', $testService->argument1);
        $this->assertEquals('argument2', $testService->argument2);

        $this->assertEquals('bar', $testService->containerValue);
        $this->assertEquals('value', $testService->scalarValue);
    }

    public function testAware()
    {
        $dic = \jakulov\Container\DIContainer::getInstance(require __DIR__ .'/../src/jakulov/Container/config.php');

        /** @var Service\TestService $testService */
        $testService = $dic->get('service.test');

        /** @var Service\AnotherTestService $anotherTestService */
        $anotherTestService = $dic->get('service.another_test');
        $this->assertEquals($anotherTestService, $testService->anotherTestService);

        /** @var Service\InterfaceTestService $interfaceTestService */
        $interfaceTestService = $dic->get('service.interface_test');
        $this->assertEquals($interfaceTestService, $anotherTestService->interfaceTestService);
    }


}

