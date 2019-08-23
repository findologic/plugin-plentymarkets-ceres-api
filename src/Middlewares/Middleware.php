<?php

namespace Findologic\Middlewares;

use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;
use Exception;
use Findologic\Constants\Plugin;
use Findologic\Exception\AliveException;
use IO\Helper\ComponentContainer;
use IO\Helper\ResourceContainer;
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
        if (!$this->pluginConfig->getShopKey()) {
            return;
        }

        if (!$this->searchService->aliveTest()) {
            $this->getLoggerObject()->error('FINDOLOGIC search is not available!');

            return;
        }

        $this->isSearchPage = strpos($request->getUri(), '/search') !== false;
        $this->activeOnCatPage = !$this->isSearchPage && $this->pluginConfig->get(Plugin::CONFIG_NAVIGATION_ENABLED);

        $this->eventDispatcher->listen(
            'IO.Resources.Import',
            function (ResourceContainer $container) {
                $container->addScriptTemplate(
                    'Findologic::content.scripts',
                    [
                        'shopkey' => strtoupper(md5($this->pluginConfig->getShopKey())),
                        'searchResultContainer' => $this->pluginConfig->get(Plugin::CONFIG_SEARCH_RESULT_CONTAINER),
                        'navigationContainer' => $this->pluginConfig->get(Plugin::CONFIG_NAVIGATION_CONTAINER),
                        'isSearchPage' => $this->isSearchPage,
                        'activeOnCatPage' => $this->activeOnCatPage
                    ]
                );

                $container->addStyleTemplate('Findologic::content.styles');
            }, 0
        );

        if ($this->isSearchPage || $this->activeOnCatPage) {
            $this->eventDispatcher->listen(
                'Ceres.Search.Options',
                function (ExternalSearchOptions $searchOptions) use ($request) {
                    $this->searchService->handleSearchOptions($request, $searchOptions);
                }
            );

            $this->eventDispatcher->listen('IO.Component.Import', function(ComponentContainer $container) {
                if( $container->getOriginComponentTemplate() === 'Ceres::ItemList.Components.Filter.ItemFilter') {
                    $container->setNewComponentTemplate('Findologic::ItemList.Components.Filter.ItemFilter');
                }
            }, 0);
        }

        $this->eventDispatcher->listen(
            'Ceres.Search.Query',
            function (ExternalSearch $externalSearch) use ($request) {
                $this->searchService->handleSearchQuery($request, $externalSearch);
            }
        );
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
