<?php

namespace Findologic\Api\Response;

use Findologic\Constants\Plugin;
use Plenty\Plugin\Translation\Translator;

/**
 * Class Response
 * @package Findologic\Api\Response
 */
class Response
{
    const DATA_SERVERS = 'servers';
    const DATA_QUERY = 'query';
    const DATA_LANDING_PAGE = 'landing_page';
    const DATA_PROMOTION = 'promotion';
    const DATA_RESULTS = 'results';
    const DATA_PRODUCTS = 'products';
    const DATA_FILTERS = 'filters';
    const DATA_QUERY_INFO_MESSAGE = 'query_info_message';

    protected $data = [];

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getData($key = null, $default = null)
    {
        if (!$key) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @return array
     */
    public function getProductsIds()
    {
        $ids = [];

        if ($products = $this->getData(self::DATA_PRODUCTS)) {
            $ids = array_column($products, 'id');
        }

        return $ids;
    }

    /**
     * @return array
     */
    public function getVariationIds()
    {
        $ids = [];

        if ($products = $this->getData(self::DATA_PRODUCTS)) {
            foreach ($products as $product) {
                if (isset($product['properties'][Plugin::API_PROPERTY_VARIATION_ID])) {
                    $ids[] = (int)$product['properties'][Plugin::API_PROPERTY_VARIATION_ID];
                }
            }
        }

        return $ids;
    }

    /**
     * @return int
     */
    public function getResultsCount()
    {
        if (!isset($this->data[self::DATA_RESULTS]['count'])) {
            return 0;
        }

        return $this->data[self::DATA_RESULTS]['count'];
    }

    public function getQueryInfoMessage(): string
    {
        $dataQueryInfoMessage = $this->getData(self::DATA_QUERY_INFO_MESSAGE);

        if (empty($dataQueryInfoMessage)) {
            return '';
        }

        $type = !empty($dataQueryInfoMessage['didYouMeanQuery'])
            ? 'did-you-mean' : $dataQueryInfoMessage['queryStringType'];
        $alternativeQuery = !empty($dataQueryInfoMessage['didYouMeanQuery'])
            ? $dataQueryInfoMessage['didYouMeanQuery']
            : $dataQueryInfoMessage['currentQuery'];
        $originalQuery = !empty($dataQueryInfoMessage['didYouMeanQuery'])
            ? $dataQueryInfoMessage['currentQuery']
            : $dataQueryInfoMessage['originalQuery'];

        if ($alternativeQuery) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'originalQuery' => $originalQuery,
                    'alternativeQuery' => $alternativeQuery
                ]
            );
        } else if ($dataQueryInfoMessage['currentQuery']
            && !empty($dataQueryInfoMessage['currentQuery'])) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => $dataQueryInfoMessage['currentQuery'],
                    'hits' => $this->getResultsCount()
                ]
            );
        } else if ($dataQueryInfoMessage['selectedCategoryName']) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageCat',
                [
                    'filterName' => $this->getCategoryFilterName(),
                    'cat' => $dataQueryInfoMessage['selectedCategoryName'],
                    'hits' => $this->getResultsCount()
                ]
            );
        } else if ($dataQueryInfoMessage['selectedVendorName']) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageVendor',
                [
                    'filterName' => $this->getVendorFilterName(),
                    'vendor' => $dataQueryInfoMessage['selectedVendorName'],
                    'hits' => $this->getResultsCount()
                ]
            );
        } else {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageDefault',
                [
                    'hits' => $this->getResultsCount()
                ]
            );
        }
    }

    public function getSmartDidYouMean(): string
    {
        $dataQueryInfoMessage = $this->getData(self::DATA_QUERY_INFO_MESSAGE);

        if (empty($dataQueryInfoMessage)) {
            return '';
        }

        $type = !empty($dataQueryInfoMessage['didYouMeanQuery'])
            ? 'did-you-mean' : $dataQueryInfoMessage['queryStringType'];
        $alternativeQuery = !empty($dataQueryInfoMessage['didYouMeanQuery'])
            ? $dataQueryInfoMessage['didYouMeanQuery']
            : $dataQueryInfoMessage['currentQuery'];
        $originalQuery = !empty($dataQueryInfoMessage['didYouMeanQuery'])
            ? $dataQueryInfoMessage['currentQuery']
            : $dataQueryInfoMessage['originalQuery'];

        if ($type === 'corrected') {
            return $this->translator->trans(
                'Findologic::Template.correctedQuery',
                [
                    'originalQuery' => $originalQuery,
                    'alternativeQuery' => $alternativeQuery
                ]
            );
        } else if ($type === 'improved') {
            return $this->translator->trans(
                'Findologic::Template.improvedQuery',
                [
                    'originalQuery' => $originalQuery,
                    'alternativeQuery' => $alternativeQuery
                ]
            );
        } else if ($type === 'did-you-mean') {
            return $this->translator->trans(
                'Findologic::Template.didYouMeanQuery',
                [
                    'originalQuery' => $originalQuery,
                    'alternativeQuery' => $alternativeQuery
                ]
            );
        } else {
            return '';
        }
    }

    /**
     * @return string|null
     */
    private function getCategoryFilterName()
    {
        if (empty($filters = $this->getData(Response::DATA_FILTERS))) {
            return null;
        }

        foreach ($filters as $filter) {
            if ($filter['id'] === 'cat') {
                return $filter['name'];
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getVendorFilterName()
    {
        if (empty($filters = $this->getData(Response::DATA_FILTERS))) {
            return null;
        }

        foreach ($filters as $filter) {
            if ($filter['id'] === 'vendor') {
                return $filter['name'];
            }
        }

        return null;
    }
}
