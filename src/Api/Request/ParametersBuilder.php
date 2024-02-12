<?php

namespace Findologic\Api\Request;

use Exception;
use Findologic\Constants\Plugin;
use Findologic\Helpers\Tags;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Plugin\Log\LoggerFactory;
use IO\Services\CategoryService;
use Ceres\Helper\ExternalSearch;
use Plenty\Plugin\Http\Request as HttpRequest;


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

    /**
     * @var Tags
     */
    protected $tagsHelper;

    public function __construct(
        LoggerFactory $loggerFactory,
        Tags $tagsHelper
    ) {
        $this->logger = $loggerFactory->getLogger(
            Plugin::PLUGIN_NAMESPACE,
            Plugin::PLUGIN_IDENTIFIER
        );
        $this->tagsHelper = $tagsHelper;
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
     * @param HttpRequest $httpRequest
     * @param ExternalSearch $externalSearch
     * @param Category|null $category
     * @return array
     */
    public function setSearchParams(
        HttpRequest $httpRequest,
        ExternalSearch $externalSearch,
        $category = null
    ) {
        $request = [];
        $request['parameters'] = (array) $httpRequest->all();
        $request['externalSearch'] = [
            'searchString' => $externalSearch->searchString,
            'sorting' => $externalSearch->sorting,
            'itemsPerPage' => $externalSearch->itemsPerPage,
            'page' => $externalSearch->page,
            'categoryId' => $externalSearch->categoryId
        ];

        $request['isTagPage'] = $this->tagsHelper->isTagPage($httpRequest);
        if ($this->tagsHelper->isTagPage($httpRequest)) {
            $request['tagId'] = $this->tagsHelper->getTagIdFromUri($httpRequest);
        }
        $request['category'] = $category ? true : false;
        $request['categoryName'] = $this->getCategoryName($category);

        return $request;
    }

    /**
     * @param ?Category $category
     * @return string
     */
    public function getCategoryName($category)
    {
        $categoryName = '';

        try {
            $categoryTree = $this->getCategoryTree($category);
            $categoryName = implode('_', $categoryTree);
        } catch (Exception $e) {
            $this->logger->error('Could not get category name', ['category' => $category?->toArray()]);
            $this->logger->logException($e);
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
