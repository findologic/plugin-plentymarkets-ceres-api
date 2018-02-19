<?php

namespace Findologic\PluginPlentymarketsApi\Providers;

use Findologic\PluginPlentymarketsApi\Services\SearchService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;

/**
 * Class FindologicServiceProvider
 * @package Findologic\Providers
 */
class FindologicServiceProvider extends ServiceProvider
{
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
        if (!$configRepository->get('findologic.enabled', false)) {
            return;
        }

        $eventDispatcher->listen(
            'IO.Search.Options',
            function(\IO\Helper\SearchOptions $searchOptions) use ($searchService, $request) {
                $searchService->handleSearchOptions($searchOptions, $request);
            }
        );

        $eventDispatcher->listen(
            'IO.Search.Query',
            function(\IO\Helper\SearchQuery $searchQuery) use ($searchService, $request) {
                $searchService->handleSearchQuery($searchQuery, $request);
            }
        );
    }
}