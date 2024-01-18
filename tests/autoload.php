<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Composer\Autoload\ClassLoader;

$classLoader = new ClassLoader();
$classLoader->addPsr4("Plenty\\", __DIR__ . '/../vendor/plentymarkets/plugin-interface', true);
$classLoader->addPsr4("Ceres\\", __DIR__ . '/../vendor/plentymarkets/plugin-ceres/src', true);
$classLoader->addPsr4("IO\\", __DIR__ . '/../vendor/plentymarkets/plugin-io/src', true);
$classLoader->add('Findologic\Tests', __DIR__);

/**
 * Add class instances that may be defined in tests. In production this is defined by Plentymarkets.
 *
 * @var array<string, object> $classInstances
 */
global $classInstances;
$classInstances = [];
if (!function_exists('replaceInstanceByMock')) {
    function replaceInstanceByMock(
        string $abstract,
        $mock
    ) {
        global $mockClassList;
        $mockClassList[$abstract] = $mock;
    }
}
if (!function_exists('pluginApp')) {
    function pluginApp(
        string $abstract,
        array $parameters = []
    ) {
        try {
            $reflector = new ReflectionClass($abstract);
        } catch (ReflectionException $e) {
            throw new Exception("Target class [$abstract] does not exist.", 0, $e);
        }

        global $mockClassList;
        if (isset($mockClassList[$abstract])) {
            return $mockClassList[$abstract];
        }

        if ($reflector->isAbstract()) {
            var_dump(['getNamespaceName' => $reflector->getNamespaceName()]);
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return new $abstract();
        }

        return new $abstract(...array_values($parameters));
    }
}

$classLoader->register();
