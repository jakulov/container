<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 12.12.15
 * Time: 23:54
 */

namespace Service;

/**
 * Class AnotherTestService
 * @package Service
 */
class AnotherTestService implements InterfaceTestServiceAwareInterface
{
    public $interfaceTestService;

    /**
     * @param InterfaceTestService $interfaceTestService
     * @return mixed
     */
    public function setInterfaceTest(InterfaceTestService $interfaceTestService)
    {
        $this->interfaceTestService = $interfaceTestService;
    }

}