<?php

namespace Findologic\Struct\QueryInfoMessage;

use Findologic\Constants\Plugin;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\Result\Filter;
use Findologic\Api\Response\Filter\BaseFilter;
use Plenty\Plugin\Log\Loggable;

class QueryInfoMessageFactory
{
    use Loggable;
    protected Response $response;
    protected string $queryString;

    protected ?int $count;

    public function __construct(
        Response $response,
        string $queryString,
        ?int $count
    ) {
        $this->response = $response;
        $this->queryString = $queryString;
        $this->count = $count;
    }

    public function getQueryInfoMessage(array $params): QueryInfoMessage
    {
        if ($this->hasAlternativeQuery()) {
            return $this->buildSearchTermQueryInfoMessage($this->response->getResult()->getMetadata()->getEffectiveQuery());
        } else if ($this->isFilterSet($params['attrib'], 'wizard')) {
            return $this->buildShoppingGuideInfoMessage($params);
        } else if ($this->hasQuery()) {
            return $this->buildSearchTermQueryInfoMessage();
        } else if ($this->isFilterSet($params['attrib'], 'cat')) {
            return $this->buildCategoryQueryInfoMessage($params);
        }

        $vendorFilterValues = $this->getFilterValues($params, BaseFilter::VENDOR_FILTER_NAME);
        if (
            $vendorFilterValues &&
            count($vendorFilterValues) === 1
        ) {
            return $this->buildVendorQueryInfoMessage(current($vendorFilterValues));
        }

        return QueryInfoMessage::buildInstance(QueryInfoMessage::TYPE_DEFAULT);
    }

    private function buildShoppingGuideInfoMessage(array $params): ShoppingGuideInfoMessage
    {
        /** @var ShoppingGuideInfoMessage $queryInfoMessage */
        $queryInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_SHOPPING_GUIDE,
            $params['attrib']['wizard']
        );

        return $queryInfoMessage;
    }

    private function buildVendorQueryInfoMessage(string $value): VendorInfoMessage
    {
        /** @var Filter[] $filters */
        $filters = array_merge(
            $this->response->getResult()->getMainFilters() ?? [],
            $this->response->getResult()->getOtherFilters() ?? []
        );

        $vendorFilter = array_filter(
            $filters,
            static fn (Filter $filter) => $filter->getName() === BaseFilter::VENDOR_FILTER_NAME
        );

        if ($vendorFilter && count($vendorFilter) === 1) {
            $filterName = array_values($vendorFilter)[0]->getDisplayName();
        }

        /** @var VendorInfoMessage $vendorInfoMessage */
        $vendorInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_VENDOR,
            null,
            $filterName,
            $value
        );

        return $vendorInfoMessage;
    }

    private function buildSearchTermQueryInfoMessage(?string $effectiveQuery = ''): SearchTermQueryInfoMessage
    {
        /** @var SearchTermQueryInfoMessage $queryInfoMessage */
        $queryInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_QUERY,
            $effectiveQuery ?: $this->queryString,
            null,
            null,
            $this->count
        );

        return $queryInfoMessage;
    }

    private function isFilterSet(?array $params, string $name): bool
    {
        if(!$params){
            return false;
        }
        return isset($params[$name]) && !empty($params[$name]);
    }

    private function getFilterValues(array $params, string $name): ?array
    {
        if (!$this->isFilterSet($params, $name)) {
            return null;
        }

        return $params[$name];
    }

    private function hasQuery(): bool
    {
        return !empty($this->queryString);
    }

    private function hasAlternativeQuery(): bool
    {
        $correctedQuery = $this->response->getResult()->getVariant()->getCorrectedQuery();
        $improvedQuery = $this->response->getResult()->getVariant()->getImprovedQuery();
        $didYouMeanQuery = $this->response->getResult()->getVariant()->getDidYouMeanQuery();

        return !empty($this->queryString) && ($correctedQuery || $improvedQuery || $didYouMeanQuery);
    }

    private function buildCategoryQueryInfoMessage(array $params): CategoryInfoMessage
    {
        /** @var Filter[] $filters */
        $filters = array_merge(
            $this->response->getResult()->getMainFilters() ?? [],
            $this->response->getResult()->getOtherFilters() ?? []
        );

        $category = end($params['attrib']['cat']);

        $catFilter = array_filter(
            $filters,
            static fn (Filter $filter) => $filter->getName() === BaseFilter::CAT_FILTER_NAME
        );

        if ($catFilter && count($catFilter) === 1) {
            $filterName = array_values($catFilter)[0]->getDisplayName();
        }

        /** @var CategoryInfoMessage $categoryInfoMessage */
        $categoryInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_CATEGORY,
            null,
            $filterName,
            $category
        );

        return $categoryInfoMessage;
    }
}
