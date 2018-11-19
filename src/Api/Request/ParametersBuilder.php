<?php

namespace Findologic\Api\Request;

use Findologic\Constants\Plugin;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use IO\Services\CategoryService;

class ParametersBuilder
{
    /**
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * @var LoggerContract
     */
    protected $logger;

    public function __construct( LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @return CategoryService|null
     */
    public function getCategoryService()
    {
        if (!$this->categoryService) {
            $this->categoryService = pluginApp(CategoryService::class);
        }

        return $this->categoryService;
    }

    /**
     * @param Request $request
     * @param HttpRequest $httpRequest
     * @param Category|null $category
     * @return Request
     */
    public function setSearchParams($request, $httpRequest, $category = null)
    {
        $parameters = $httpRequest->all();

        $request->setParam('query', $parameters['query'] ?? '');
        $request->setPropertyParam(Plugin::API_PROPERTY_MAIN_VARIATION_ID);

        if (isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES])) {
            $attributes = $parameters[Plugin::API_PARAMETER_ATTRIBUTES];
            foreach ($attributes as $key => $value) {
                if ($key === 'cat' && $category) {
                    continue;
                }

                $request->setAttributeParam($key, $value);
            }
        }

        if ($category && ($categoryFullName = $this->getCategoryName($category))) {
            $request->setParam('selected', ['cat' => [$categoryFullName]]);
            //TODO: remove after testing
            $this->logger->error('Category full name: ' . $categoryFullName);
        }

        if (isset($parameters['sorting']) && in_array($parameters['sorting'], Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)) {
            $request->setParam(Plugin::API_PARAMETER_SORT_ORDER, $parameters['sorting']);
        }

        $request = $this->setPagination($request, $parameters);

        return $request;
    }

    /**
     * @param Request $request
     * @param array $parameters
     * @return Request
     */
    protected function setPagination($request, array $parameters)
    {
        $pageSize = intval($parameters['items'] ?? 0);

        if ($pageSize > 0) {
            $request->setParam(Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE, $pageSize);
        }

        $paginationStart = intval($parameters['page'] ?? 0) * $pageSize;

        if ($paginationStart > 0) {
            $request->setParam(Plugin::API_PARAMETER_PAGINATION_START, $paginationStart);
        }

        return $request;
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getCategoryName($category)
    {
        $categoryName = '';

        try {
            $categoryTree = $this->getCategoryTree($category);
            $categoryName = implode('_', $categoryTree);
        } catch (\Exception $e) {
            $this->logger->error('Could not get category name. ' . $e->getMessage(), $e->getTrace());
        }

        return $categoryName;
    }

    /**
     * @param Category $category
     * @param array $categoryTree
     * @return array
     */
    protected function getCategoryTree($category, $categoryTree = [])
    {
        if (!$category->details || !isset($category->details[0]) || !($details = $category->details[0])) {
            return $categoryTree;
        }

        $categoryTree[] = $details->name;

        if ($category->parentCategoryId) {
            $parentCategory = $this->getCategoryService()->get($category->parentCategoryId);
            return $this->getCategoryTree($parentCategory, $categoryTree);
        }

        return $categoryTree;
    }
}