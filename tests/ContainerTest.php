<?php

/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 12.12.15
 * Time: 23:35
 */
class ContainerTest extends PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $container = \jakulov\Container\Container::getInstance(['key' => 'value']);

        $this->assertEquals('value', $container->get('key'));
    }

    public function testGetWithDot()
    {
        $container = \jakulov\Container\Container::getInstance(['key' => [
            'key1' => 'value1'
        ]]);

        $this->assertEquals('value1', $container->get('key.key1'));
    }

    public function testGetArrayValue()
    {
        $container = \jakulov\Container\Container::getInstance(['key' => ['key1' => 'value1']]);

        $this->assertEquals(['key1' => 'value1'], $container->get('key'));
    }

    public function testGetDefaultValue()
    {
        $container = \jakulov\Container\Container::getInstance([]);

        $this->assertEquals('foo', $container->get('key', 'foo'));
    }


}
