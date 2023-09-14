<?php
declare(strict_types=1);

namespace FINDOLOGIC\Struct\QueryInfoMessage;

use Findologic\Constants\Plugin;
use FINDOLOGIC\Response\Filter\BaseFilter;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter as ApiFilter;

class QueryInfoMessageFactory {

    public function __construct(
        protected readonly Json10Response $response,
        protected readonly string $queryString
    ){}

    public function getQueryInfoMessage(array $params): QueryInfoMessage {
        if ($this->hasAlternativeQuery()) {
            return $this->buildSearchTermQueryInfoMessage($this->response->getResult()->getMetadata()->getEffectiveQuery());
        }
        else if ($this->isFilterSet($params['attrib'], 'wizard')) {
            return QueryInfoMessage::TYPE_SHOPPING_GUIDE;
        }
        else if ($this->hasQuery()) {
            return $this->buildSearchTermQueryInfoMessage();
        }
        else if ($this->isFilterSet($params['attrib'], 'cat')) {
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

    private function buildVendorQueryInfoMessage(string $value): VendorInfoMessage
    {
        /** @var ApiFilter[] $filters */
        $filters = array_merge(
            $this->response->getResult()->getMainFilters() ?? [],
            $this->response->getResult()->getOtherFilters() ?? []
        );

        $vendorFilter = array_filter(
            $filters,
            static fn (ApiFilter $filter) => $filter->getName() === BaseFilter::VENDOR_FILTER_NAME
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

    private function buildSearchTermQueryInfoMessage(): SearchTermQueryInfoMessage
    {
        /** @var SearchTermQueryInfoMessage $queryInfoMessage */
        $queryInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_QUERY,
            $this->queryString
        );

        return $queryInfoMessage;
    }

    private function isFilterSet(array $params, string $name): bool
    {
        return isset($params[$name]) && !empty($params[$name]);
    }

    private function getFilterValues(array $params, string $name): ?array
    {
        if (!$this->isFilterSet($params, $name)) {
            return null;
        }

        $filterValues = [];
        $joinedFilterValues = explode(Plugin::FILTER_HANDLER_DELIMITER, $params[$name]);

        foreach ($joinedFilterValues as $joinedFilterValue) {
            $filterValues[] = str_contains($joinedFilterValue, Plugin::FILTER_VALUE_DELIMITER)
                ? explode(Plugin::FILTER_VALUE_DELIMITER, $joinedFilterValue)[1]
                : $joinedFilterValue;
        }

        return $filterValues;
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
        /** @var ApiFilter[] $filters */
        $filters = array_merge(
            $this->response->getResult()->getMainFilters() ?? [],
            $this->response->getResult()->getOtherFilters() ?? []
        );

        $categories = explode('_', $params['attrib']['cat']);
        $category = end($categories);

        $catFilter = array_filter(
            $filters,
            static fn (ApiFilter $filter) => $filter->getName() === BaseFilter::CAT_FILTER_NAME
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
 