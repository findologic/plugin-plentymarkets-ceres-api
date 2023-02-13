<?php

namespace Findologic\Providers;

use Ceres\Helper\LayoutContainer;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Events\Dispatcher;
use Findologic\Middlewares\Middleware;
use Findologic\Services\SearchService;
use Findologic\Components\PluginConfig;
use Findologic\Validators\PluginConfigurationValidator;

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
        // $this->getApplication()->register(FindologicTemplateProvider::class);

        $this->addGlobalMiddleware(Middleware::class);
    }

    public function boot(Twig $twig, Dispatcher $eventDispatcher,)
    {
        $eventDispatcher->listen("Ceres.LayoutContainer.Script.Loader", function(LayoutContainer $container) use ($twig) {
            $container->addContent($twig->render("Findologic::content.translations"));
        });
    }
}
