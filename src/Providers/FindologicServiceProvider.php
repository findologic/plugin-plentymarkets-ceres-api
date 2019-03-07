<?php

namespace Findologic\Providers;

use Findologic\Constants\Plugin;
use Findologic\Services\SearchService;
use Ceres\Helper\ExternalSearchOptions;
use Ceres\Helper\ExternalSearch;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Log\Loggable;
use Plenty\Log\Contracts\LoggerContract;
use IO\Helper\ResourceContainer;
use IO\Helper\ComponentContainer;

/**
 * Class FindologicServiceProvider
 * @package Findologic\Providers
 */
class FindologicServiceProvider extends ServiceProvider
{
    use Loggable;

    /**
     * @var LoggerContract
     */
    protected $logger = false;

    /**
     * @var bool
     */
    protected $isSearchPage = false;

    /**
     * @var bool
     */
    protected $activeOnCatPage = false;

    public function register()
    {
        $this->getApplication()->singleton(SearchService::class);
    }

    /**
     * @param ConfigRepository $configRepository
     * @param Dispatcher $eventDispatcher
     * @param Request $request
     * @param SearchService $searchService
     */
    public function boot(
        ConfigRepository $configRepository,
        Dispatcher $eventDispatcher,
        Request $request,
        SearchService $searchService
    ) {
        if (!$configRepository->get(Plugin::CONFIG_SHOPKEY, false)) {
            return;
        }

        $this->isSearchPage = strpos($request->getUri(), '/search') !== false;
        $this->activeOnCatPage = !$this->isSearchPage && $configRepository->get(
                Plugin::CONFIG_NAVIGATION_ENABLED);

        $eventDispatcher->listen(
            'IO.Resources.Import',
            function (ResourceContainer $container) use ($configRepository) {
                $container->addScriptTemplate(
                    'Findologic::content.scripts',
                    [
                        'shopkey' => strtoupper(md5($configRepository->get(Plugin::CONFIG_SHOPKEY, ''))),
                        'isSearchPage' => $this->isSearchPage,
                        'activeOnCatPage' => $this->activeOnCatPage,
                    ]
                );

                $container->addStyleTemplate('Findologic::content.styles');
            }, 0
        );

        if ($this->isSearchPage || $this->activeOnCatPage) {
            $eventDispatcher->listen(
                'Ceres.Search.Options',
                function (ExternalSearchOptions $searchOptions) use ($searchService, $request) {
                    $searchService->handleSearchOptions($request, $searchOptions);
                }
            );

            $eventDispatcher->listen('IO.Component.Import', function(ComponentContainer $container) {
                if( $container->getOriginComponentTemplate() === 'Ceres::ItemList.Components.Filter.ItemFilter') {
                    $container->setNewComponentTemplate('Findologic::ItemList.Components.Filter.ItemFilter');
                }
            }, 0);
        }

        $eventDispatcher->listen(
            'Ceres.Search.Query',
            function (ExternalSearch $externalSearch) use ($searchService, $request) {
                $searchService->handleSearchQuery($request, $externalSearch);
            }
        );
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