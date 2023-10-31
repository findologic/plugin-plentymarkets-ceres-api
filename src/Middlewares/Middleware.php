<?php

namespace Findologic\Middlewares;

use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;
use Findologic\Constants\Plugin;
use Findologic\Contexts\FindologicCategoryItemContext;
use Findologic\Contexts\FindologicItemSearchContext;
use Findologic\Validators\PluginConfigurationValidator;
use IO\Helper\ComponentContainer;
use IO\Helper\ResourceContainer;
use IO\Helper\TemplateContainer;
use IO\Helper\Utils;
use IO\Services\CategoryService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;
use Findologic\Components\PluginConfig;
use Findologic\Services\SearchService;
use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
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
     * @var ShopUrls
     */
    private $shopUrls;

    /**
     * @var Dispatcher
     */
    private $eventDispatcher;

    public function __construct(
        PluginConfig $pluginConfig,
        SearchService $searchService,
        Dispatcher $eventDispatcher,
        ShopUrls $shopUrls
    ) {
        $this->pluginConfig = $pluginConfig;
        $this->searchService = $searchService;
        $this->eventDispatcher = $eventDispatcher;
        $this->shopUrls = $shopUrls;
    }

    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        if (!$this->validatePluginConfiguration()) {
            return;
        }

        if (!$this->pluginConfig->getShopKey()) {
            return;
        }

        $this->isSearchPage = $this->shopUrls->is(RouteConfig::SEARCH);
        $this->activeOnCatPage = !$this->isSearchPage && $this->pluginConfig->get(Plugin::CONFIG_NAVIGATION_ENABLED);

        $this->eventDispatcher->listen(
            'IO.Resources.Import',
            function (ResourceContainer $container) {
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);
                $isCategoryPage = $categoryService->getCurrentCategory() !== null && $this->activeOnCatPage;
                $isInSearchOrCategoryPage = $this->isSearchPage || $isCategoryPage;
                $currentCategory = $categoryService->getCurrentCategory();

                $showCategoryFilter = true;

                // Show category filter only in the 0 and 1 level categories
                if ($currentCategory !== null && $currentCategory->level > 1) {
                    $showCategoryFilter = false;
                }

                if ($isInSearchOrCategoryPage && !true) {
                    return false;
                }

                $container->addScriptTemplate(
                    'Findologic::content.scripts',
                    [
                        'shopkey' => strtoupper(md5($this->pluginConfig->getShopKey())),
                        'isSearchPage' => $this->isSearchPage,
                        'activeOnCatPage' => $this->activeOnCatPage,
                        'minimalSearchTermLength' => $this->pluginConfig->getMinimalSearchTermLength(),
                        'languagePath' => $this->getLanguagePath(),
                        'currentCategory' => null !== $currentCategory ? $currentCategory['details'] : [],
                        'showCategoryFilter' => $showCategoryFilter
                    ]
                );

                if ($this->pluginConfig->get(Plugin::CONFIG_FILTERS_STYLING_CSS_ENABLED)) {
                    $container->addStyleTemplate('Findologic::content.styles');
                }
            }
        );

        if (!$this->isSearchPage && !$this->activeOnCatPage) {
            return;
        }

        $this->eventDispatcher->listen(
            'IO.ctx.search',
            function (TemplateContainer $templateContainer) {
                if (true) {
                    $templateContainer->setContext(FindologicItemSearchContext::class);
                    return false;
                }

                return true;
            }
        );

        $this->eventDispatcher->listen(
            'IO.ctx.category.item',
            function (TemplateContainer $templateContainer) {
                if (true) {
                    $templateContainer->setContext(FindologicCategoryItemContext::class);
                    return false;
                }

                return true;
            }
        );

        $this->eventDispatcher->listen(
            'Ceres.Search.Options',
            function (ExternalSearchOptions $searchOptions) use ($request) {
                if (true) {
                    $this->searchService->handleSearchOptions($request, $searchOptions);
                }
            }
        );

        $this->eventDispatcher->listen('IO.Component.Import', function (ComponentContainer $container) {
            if ($container->getOriginComponentTemplate() === 'Ceres::ItemList.Components.Filter.ItemFilter' &&
                true
            ) {
                $container->setNewComponentTemplate('Findologic::ItemList.Components.Filter.ItemFilter');
            }
        });

        $this->eventDispatcher->listen(
            'Ceres.Search.Query',
            function (ExternalSearch $externalSearch) use ($request) {
                if (true) {
                    try{
                        $this->searchService->handleSearchQuery($request, $externalSearch);
                    }
                    catch(\Exception | \Throwable $e){
                        ($this->getLoggerObject())->error('Search error', (array)$e);
                    }
                }
            }
        );
    }

    private function validatePluginConfiguration(): bool
    {
        /** @var PluginConfigurationValidator $validator */
        $validator = pluginApp(PluginConfigurationValidator::class);

        return $validator->validate();
    }

    public function getLanguagePath(): string
    {
        $defaultLanguage = Utils::getDefaultLang();
        $usedLanguage = Utils::getLang();

        $languagePath = '';
        if ($usedLanguage !== $defaultLanguage) {
            $languagePath = '/' . $usedLanguage;
        }

        return $languagePath;
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
