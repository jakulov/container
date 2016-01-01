<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 12.12.15
 * Time: 23:53
 */

namespace Service;

/**
 * Class TestService
 * @package Service
 */
class TestService
{
    public $argument1;
    public $argument2;

    public $anotherTestService;

    public $containerValue;

    public $scalarValue;
    /** @var RequestTestService */
    public $requestTestService;

    public function __construct($argument1, $argument2)
    {
        $this->argument1 = $argument1;
        $this->argument2 = $argument2;
    }

    public function setAnotherTestService(AnotherTestService $anotherTestService)
    {
        $this->anotherTestService = $anotherTestService;
    }

    public function setContainerValue($containerValue)
    {
        $this->containerValue = $containerValue;
    }

    public function setScalarValue($scalarValue)
    {
        $this->scalarValue = $scalarValue;
    }

    public function setRequestTest(RequestTestService $requestTestService)
    {
        $this->requestTestService = $requestTestService;
    }

}