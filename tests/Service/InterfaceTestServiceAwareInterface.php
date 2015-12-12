<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 12.12.15
 * Time: 23:54
 */

namespace Service;

/**
 * Interface InterfaceTestServiceAwareInterface
 * @package Service
 */
interface InterfaceTestServiceAwareInterface
{
    /**
     * @param InterfaceTestService $interfaceTestService
     * @return void
     */
    public function setInterfaceTest(InterfaceTestService $interfaceTestService);
}