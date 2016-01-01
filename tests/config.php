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
                'setRequestTest' => '@request_test',
            ],
        ],
        'another_test' => [
            'class' => 'Service\\AnotherTestService',
        ],
        'alias_test' => '@another_test',
        'interface_test' => [
            'class' => 'Service\\InterfaceTestService'
        ],
        'child_test' => [
            'parent' => '@test',
            'aware' => [
                'setScalarValue' => 'child_value'
            ],
        ],
        'request_test' => [
            'class' => 'Service\\RequestTestService'
        ],
    ],
];