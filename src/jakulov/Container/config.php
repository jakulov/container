<?php

/**
 * Example container config with services and DI
 */
return [
    'foo' => 'bar',
    'container' => [
        'di' => [
            'aware' => [
                'Service\\InterfaceTestServiceAwareInterface' => [
                    'setInterfaceTest' => '@service.interface_test',
                ],
            ],
        ],
    ],
    'service' => [
        'test' => [
            'class' => 'Service\\TestService',
            'args' => [
                'argument1',
                'argument2'
            ],
            'aware' => [
                'setAnotherTestService' => '@service.another_test',
                'setContainerValue' => ':foo',
                'setScalarValue' => 'value',
            ],
        ],
        'another_test' => [
            'class' => 'Service\\AnotherTestService',
        ],
        'interface_test' => [
            'class' => 'Service\\InterfaceTestService'
        ],
    ],
];