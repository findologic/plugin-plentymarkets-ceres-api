<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Composer\Autoload\ClassLoader;

$classLoader = new ClassLoader();
$classLoader->addPsr4("Plenty\\", __DIR__ . '/../vendor/plentymarkets/plugin-interface', true);
$classLoader->addPsr4("Ceres\\", __DIR__ . '/../vendor/plentymarkets/plugin-ceres/src', true);
$classLoader->addPsr4("IO\\", __DIR__ . '/../vendor/plentymarkets/plugin-io/src', true);
$classLoader->add('Findologic\Tests', __DIR__);

$classLoader->register();