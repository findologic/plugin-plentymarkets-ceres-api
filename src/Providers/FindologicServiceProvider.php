<?php

namespace Findologic\Providers;

use Findologic\Components\PluginConfig;
use Findologic\Contexts\FindologicCategoryItemContext;
use Findologic\Contexts\FindologicItemSearchContext;
use Findologic\Services\SearchService;
use IO\Helper\TemplateContainer;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Findologic\Middlewares\Middleware;
use Plenty\Plugin\Templates\Twig;

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
        $this->addGlobalMiddleware(Middleware::class);
    }

    public function boot(Twig $twig, Dispatcher $eventDispatcher)
    {
        $eventDispatcher->listen('IO.ctx.search', function (TemplateContainer $templateContainer, $templateData = []) {
            $templateContainer->setContext(FindologicItemSearchContext::class);
            return false;
        });
        $eventDispatcher->listen(
            'IO.ctx.category.item',
            function (TemplateContainer $templateContainer, $templateData = []) {
                $templateContainer->setContext(FindologicCategoryItemContext::class);
                return false;
            }
        );
    }
}
