<?php

include_once __DIR__.'/../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Plenty\\", __DIR__.'/../vendor/plentymarkets/plugin-interface', true);
$classLoader->addPsr4("Ceres\\", __DIR__.'/../vendor/plentymarkets-ceres/plugin-ceres/src', true);
$classLoader->register();