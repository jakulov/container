<?php

require __DIR__ .'/Service/InterfaceTestServiceAwareInterface.php';
require __DIR__ .'/Service/InterfaceTestService.php';
require __DIR__ .'/Service/AnotherTestService.php';
require __DIR__ .'/Service/TestService.php';
require __DIR__ .'/Service/UnresolvedTestService.php';
require __DIR__ .'/Service/RequestTestService.php';


class DIContainerTest extends PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $config = require __DIR__ . '/config.php';
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
        $dic = \jakulov\Container\DIContainer::getInstance(require __DIR__ . '/config.php');

        /** @var Service\TestService $testService */
        $testService = $dic->get('service.test');

        /** @var Service\AnotherTestService $anotherTestService */
        $anotherTestService = $dic->get('service.another_test');
        $this->assertEquals($anotherTestService, $testService->anotherTestService);

        /** @var Service\InterfaceTestService $interfaceTestService */
        $interfaceTestService = $dic->get('service.interface_test');
        $this->assertEquals($interfaceTestService, $anotherTestService->interfaceTestService);
    }

    public function testAlias()
    {
        $dic = \jakulov\Container\DIContainer::getInstance(require __DIR__ . '/config.php');
        /** @var Service\AnotherTestService $anotherTestService */
        $anotherTestService = $dic->get('service.another_test');

        /** @var Service\AnotherTestService $anotherTestService */
        $anotherTestServiceByAlias = $dic->get('service.alias_test');

        $this->assertEquals($anotherTestService, $anotherTestServiceByAlias, 'alias works');
    }

    public function testParent()
    {
        $dic = \jakulov\Container\DIContainer::getInstance(require __DIR__ . '/config.php');

        /** @var Service\TestService $testService */
        $testService = $dic->get('service.test');

        /** @var Service\TestService $childService */
        $childService = $dic->get('service.child_test');

        $this->assertEquals('value', $testService->scalarValue);
        $this->assertEquals('child_value', $childService->scalarValue);
    }

    public function testResolve()
    {
        $dic = \jakulov\Container\DIContainer::getInstance(require __DIR__ . '/config.php');

        /** @var \Service\UnresolvedTestService $unresolved */
        $unresolved = $dic->resolve(\Service\UnresolvedTestService::class);

        $this->assertEquals($dic->get('service.interface_test'), $unresolved->interfaceTest);
    }

    public function testProvide()
    {
        $dic = \jakulov\Container\DIContainer::getInstance(require __DIR__ . '/config.php');

        /** @var Service\TestService $testService */
        $testService = $dic->get('test');

        $this->assertEquals(null, $testService->requestTestService->someGlobalShit, 'Request is empty');

        /** @var Service\RequestTestService $request */
        $request = $dic->get('request_test');

        $this->assertEquals(spl_object_hash($testService->requestTestService), spl_object_hash($request));

        $request->someGlobalShit = 'A';

        $this->assertEquals('A', $testService->requestTestService->someGlobalShit);

        $newRequest = new \Service\RequestTestService();
        $newRequest->someGlobalShit = 'B';

        /** @var Service\RequestTestService $providedRequest */
        $providedRequest = $dic->provide('request_test', $newRequest)->get('request_test');

        $this->assertEquals(spl_object_hash($newRequest), spl_object_hash($providedRequest), 'Provided request not new?');

        $this->assertEquals('B', $testService->requestTestService->someGlobalShit);
    }


}

