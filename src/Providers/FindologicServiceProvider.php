<?php

namespace Findologic\Providers;

use Findologic\Components\PluginConfig;
use Findologic\Services\SearchService;
use Findologic\Validators\PluginConfigurationValidator;
use Plenty\Plugin\ServiceProvider;
use Findologic\Middlewares\Middleware;

/**
 * Class FindologicServiceProvider
 * @package Findologic\Providers
 */
class FindologicServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->singleton(SearchService::class);
        $this->getApplication()->singleton(PluginConfig::class);
        $this->getApplication()->singleton(PluginConfigurationValidator::class);

        $this->addGlobalMiddleware(Middleware::class);
    }
}
