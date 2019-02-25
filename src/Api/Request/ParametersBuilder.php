<?php

namespace Findologic\Api\Request;

use Findologic\Constants\Plugin;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Plugin\Log\LoggerFactory;
use IO\Services\CategoryService;
use Ceres\Helper\ExternalSearch;
use Plenty\Plugin\Http\Request as HttpRequest;

class ParametersBuilder
{
    const SORT_MAPPING = [
        'sorting.price.avg_asc' => 'price ASC',
        'sorting.price.avg_desc' => 'price DESC',
        'texts.name1_asc' => 'label ASC',
        'texts.name1_desc' => 'label DESC',
        'default.recommended_sorting' => 'salesfrequency DESC',
        'variation.createdAt_desc' => 'dateadded DESC',
        'variation.createdAt_asc' => 'dateadded ASC'
    ];

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
     * @return CategoryService
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
     * @param ExternalSearch $externalSearch
     * @param Category|null $category
     * @return Request
     */
    public function setSearchParams(
        Request $request,
        HttpRequest $httpRequest,
        ExternalSearch $externalSearch,
        $category = null
    ) {
        $parameters = (array) $httpRequest->all();

        $request->setParam('query', $externalSearch->searchString);
        $request->setPropertyParam(Plugin::API_PROPERTY_VARIATION_ID);

        if (isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES])) {
            $attributes = $parameters[Plugin::API_PARAMETER_ATTRIBUTES];
            foreach ($attributes as $key => $value) {
                if (is_array($value)) {
                    $value = array_unique($value);
                }

                if ($key === 'cat' && $category) {
                    continue;
                }

                $request->setAttributeParam($key, $value);
            }
        }

        if ($category && ($categoryFullName = $this->getCategoryName($category))) {
            $request->setParam('selected', ['cat' => [$categoryFullName]]);
        }

        if (in_array($externalSearch->sorting, Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)) {
            $request->setParam(Plugin::API_PARAMETER_SORT_ORDER, self::SORT_MAPPING[$externalSearch->sorting]);
        }

        $request = $this->setPagination($request, $externalSearch);

        return $request;
    }

    /**
     * @param Request $request
     * @param ExternalSearch $externalSearch
     * @return Request
     */
    protected function setPagination(Request $request, ExternalSearch $externalSearch)
    {
        if (
            $externalSearch->categoryId !== null &&
            !array_key_exists(Plugin::API_PARAMETER_ATTRIBUTES, $_GET)
        ) {
            $request->setParam(Plugin::API_PARAMETER_PAGINATION_START, 0);
            $request->setParam(Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE, 0);
            return $request;
        }

        $request->setParam(Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE, $externalSearch->itemsPerPage);

        if ($externalSearch->page > 1) {
            $request->setParam(
                Plugin::API_PARAMETER_PAGINATION_START,
                ($externalSearch->page - 1) * $externalSearch->itemsPerPage
            );
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

        array_unshift($categoryTree, $details->name);

        if ($category->parentCategoryId) {
            $parentCategory = $this->getCategoryService()->get($category->parentCategoryId);
            return $this->getCategoryTree($parentCategory, $categoryTree);
        }

        return $categoryTree;
    }
}