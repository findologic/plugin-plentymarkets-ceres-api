<?php

namespace Findologic\Middlewares;

use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;
use Exception;
use Findologic\Constants\Plugin;
use Findologic\Contexts\FindologicCategoryItemContext;
use Findologic\Contexts\FindologicItemSearchContext;
use Findologic\Exception\AliveException;
use Findologic\Validators\PluginConfigurationValidator;
use IO\Helper\ComponentContainer;
use IO\Helper\ResourceContainer;
use IO\Helper\TemplateContainer;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;
use Findologic\Components\PluginConfig;
use Findologic\Services\SearchService;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Middleware as PlentyMiddleware;

/**
 * Class Middleware
 * @package Findologic\Middlewares
 */
class Middleware extends PlentyMiddleware
{
    use Loggable;

    /**
     * @var LoggerContract
     */
    private $logger;

    /**
     * @var bool
     */
    private $isSearchPage = false;

    /**
     * @var bool
     */
    private $activeOnCatPage = false;

    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var Dispatcher
     */
    private $eventDispatcher;

    public function __construct(
        PluginConfig $pluginConfig,
        SearchService $searchService,
        Dispatcher $eventDispatcher
    ) {
        $this->pluginConfig = $pluginConfig;
        $this->searchService = $searchService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        if (!$this->validatePluginConfiguration()) {
            return;
        }

        if (!$this->pluginConfig->getShopKey() || !$this->searchService->aliveTest()) {
            return;
        }

        $this->eventDispatcher->listen(
            'IO.Resources.Import',
            function (ResourceContainer $container) {
                if ($this->pluginConfig->get(Plugin::CONFIG_LOAD_NO_UI_SLIDER_STYLES_ENABLED)) {
                    $container->addScriptTemplate('Findologic::content.nouislider.noui-js');
                    $container->addStyleTemplate('Findologic::content.nouislider.noui-css');
                }

                $container->addScriptTemplate(
                    'Findologic::content.scripts',
                    [
                        'shopkey' => strtoupper(md5($this->pluginConfig->getShopKey())),
                        'isSearchPage' => $this->isSearchPage,
                        'activeOnCatPage' => $this->activeOnCatPage
                    ]
                );

                if ($this->pluginConfig->get(Plugin::CONFIG_FILTERS_STYLING_CSS_ENABLED)) {
                    $container->addStyleTemplate('Findologic::content.styles');
                }
            },
            0
        );

        $this->isSearchPage = strpos($request->getUri(), '/search') !== false;
        $this->activeOnCatPage = !$this->isSearchPage && $this->pluginConfig->get(Plugin::CONFIG_NAVIGATION_ENABLED);

        if (!$this->isSearchPage && !$this->activeOnCatPage) {
            return;
        }

        $this->eventDispatcher->listen(
            'IO.ctx.search',
            function (TemplateContainer $templateContainer, $templateData = []) {
                $templateContainer->setContext(FindologicItemSearchContext::class);
                return false;
            }
        );

        $this->eventDispatcher->listen(
            'IO.ctx.category.item',
            function (TemplateContainer $templateContainer, $templateData = []) {
                $templateContainer->setContext(FindologicCategoryItemContext::class);
                return false;
            }
        );

        $this->eventDispatcher->listen(
            'Ceres.Search.Options',
            function (ExternalSearchOptions $searchOptions) use ($request) {
                $this->searchService->handleSearchOptions($request, $searchOptions);
            }
        );

        $this->eventDispatcher->listen('IO.Component.Import', function (ComponentContainer $container) {
            if ($container->getOriginComponentTemplate() === 'Ceres::ItemList.Components.Filter.ItemFilter') {
                $container->setNewComponentTemplate('Findologic::ItemList.Components.Filter.ItemFilter');
            }
        }, 0);

        $this->eventDispatcher->listen(
            'Ceres.Search.Query',
            function (ExternalSearch $externalSearch) use ($request) {
                $this->searchService->handleSearchQuery($request, $externalSearch);
            }
        );
    }

    private function validatePluginConfiguration(): bool
    {
        /** @var PluginConfigurationValidator $validator */
        $validator = pluginApp(PluginConfigurationValidator::class);

        return $validator->validate();
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response): Response
    {
        return $response;
    }

    /**
     * @return LoggerContract
     */
    protected function getLoggerObject()
    {
        if (!$this->logger) {
            $this->logger = $this->getLogger(Plugin::PLUGIN_IDENTIFIER);
        }

        return $this->logger;
    }
}
