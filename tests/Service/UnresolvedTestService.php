<?php
namespace Service;

/**
 * Class UnresolvedService
 * @package Service
 */
class UnresolvedTestService implements InterfaceTestServiceAwareInterface
{
    public $interfaceTest;

    /**
     * @param InterfaceTestService $interfaceTestService
     * @return mixed
     */
    public function setInterfaceTest(InterfaceTestService $interfaceTestService)
    {
        $this->interfaceTest = $interfaceTestService;
    }

}