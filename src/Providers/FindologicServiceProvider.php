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

    public function register()
    {
        //$this->getApplication()->register(FindologicRouteServiceProvider::class);
    }

    /**
     * @param Dispatcher $eventDispatcher
     * @param ConfigRepository $configRepository
     * @param SearchService $searchService
     */
    public function boot(
        ConfigRepository $configRepository,
        Dispatcher $eventDispatcher,
        Request $request,
        SearchService $searchService
    ) {
        if (!$configRepository->get(Plugin::CONFIG_ENABLED, false)) {
            return;
        }

        $eventDispatcher->listen(
            'Ceres.Search.Options',
            function(ExternalSearchOptions $searchOptions) use ($searchService, $request) {
                $searchService->handleSearchOptions($searchOptions, $request);
            }
        );

        $eventDispatcher->listen(
            'Ceres.Search.Query',
            function(ExternalSearch $searchQuery) use ($searchService, $request) {
                $searchService->handleSearchQuery($searchQuery, $request);
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