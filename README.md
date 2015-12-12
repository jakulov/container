# Container #
Simple universal Container & DIContainer

Can be installed with composer

    composer require jakulov/container
    
Implements [Container Interoperability](https://github.com/container-interop/container-interop)

## 1. Container ##
Container could be used to store any array data (e.g. configuration or repository) and easy accessing it with dot notation.

    $config = ['test' => [
            'key1' => 'value1',
        ]];
    $container = \jakulov\Container\Container::getInstance($config);
    echo $container->get('test.key1'); // value1

Container and DIContainer uses singleton pattern, so after first initialization of container you can access to it without passing config as argument, like:
    
    // second and other usages of container should not use config
    $container = \jakuov\Container\Container::getInstance();
    
## 2. DIContainer ##
Dependency injection container should be used for manager dependencies between services in php application. 
This quite simple but agile implementation of DI Container. 
    
    $config = [
        'foo' => 'bar', 
        // you can use aware-interfaces to manage dependencies in app 
        'container' => [
          'di' => [
              'aware' => [
                  // in any service class implements this interface and resolved
                  // with DIContainer will be called setInterfaceTest method with argument
                  // containing instance of service "service.interface_test"
                  'Service\\InterfaceTestServiceAwareInterface' => [
                      'setInterfaceTest' => '@service.interface_test',
                  ],
              ],
          ],
        ],
        // configuration of services
        'service' => [
          // service name will be "service.test"
          'test' => [
              'class' => 'Service\\TestService', // class of service
              // arguments of service __construct
              'args' => [
                  'argument1',
                  'argument2'
              ],
              // setters to call while service initialization
              'aware' => [
                  // as dependency you can use:
                  'setAnotherTestService' => '@service.another_test', // another service
                  'setContainerValue' => ':foo', // container value
                  'setScalarValue' => 'value', // or scalar value
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
      
    $dic = \jakulov\Container\DIContainer::getInstance($config);
    $testService = $dic->get('service.test');
    echo $testService->argument1; // 'argument1'
    echo $testService->containerValue; // 'bar'
    echo $testService->scalarValue; // 'value'
    echo get_class( $testService->anotherTestService ); // Service\\AnotherTestService
    echo get_class( $testService->anotherTestService->interfaceTestService ); // Service\\InterfaceTestService
    
## Tests ##

Run:
vendor/bin/phpunit tests/

Tests are also examples for usage library