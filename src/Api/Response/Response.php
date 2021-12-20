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
    const DATA_FILTERS_WIDGETS = 'filters_widgets';
    const DATA_QUERY_INFO_MESSAGE = 'query_info_message';

    const
        DID_YOU_MEAN_QUERY = 'did-you-mean',
        CORRECTED_QUERY = 'corrected',
        IMPROVED_QUERY = 'improved';

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
     * @return Response
     */
    public static function getInstance()
    {
        return pluginApp(self::class);
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
    public function getResultsCount(): int
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

        if (!empty($dataQueryInfoMessage['shoppingGuide'])) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageShoppingGuide',
                [
                    'shoppingGuide' => $dataQueryInfoMessage['shoppingGuide'],
                    'hits' => $this->getResultsCount()
                ]
            );
        }

        $type = $dataQueryInfoMessage['queryStringType'];
        $alternativeQuery = $dataQueryInfoMessage['currentQuery'];

        if (!empty($dataQueryInfoMessage['didYouMeanQuery'])) {
            $type = self::DID_YOU_MEAN_QUERY;
            $alternativeQuery = $dataQueryInfoMessage['didYouMeanQuery'];
        }

        if ($alternativeQuery && ($type === self::CORRECTED_QUERY || $type === self::IMPROVED_QUERY)) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => $alternativeQuery,
                    'hits' => $this->getResultsCount()
                ]
            );
        } elseif ($dataQueryInfoMessage['currentQuery']
            && !empty($dataQueryInfoMessage['currentQuery'])
        ) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => $dataQueryInfoMessage['currentQuery'],
                    'hits' => $this->getResultsCount()
                ]
            );
        } elseif ($dataQueryInfoMessage['selectedCategoryName']) {
            return $this->translator->trans(
                'Findologic::Template.queryInfoMessageCat',
                [
                    'filterName' => $this->getCategoryFilterName(),
                    'cat' => $dataQueryInfoMessage['selectedCategoryName'],
                    'hits' => $this->getResultsCount()
                ]
            );
        } elseif ($dataQueryInfoMessage['selectedVendorName']) {
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

        $type = $dataQueryInfoMessage['queryStringType'];
        $alternativeQuery = $dataQueryInfoMessage['currentQuery'];
        $originalQuery = $dataQueryInfoMessage['originalQuery'];

        if (!empty($dataQueryInfoMessage['didYouMeanQuery'])) {
            $type = 'did-you-mean';
            $alternativeQuery = $dataQueryInfoMessage['didYouMeanQuery'];
            $originalQuery = $dataQueryInfoMessage['currentQuery'];
        }

        switch ($type) {
            case self::CORRECTED_QUERY:
                return $this->translator->trans(
                    'Findologic::Template.correctedQuery',
                    [
                        'originalQuery' => $originalQuery,
                        'alternativeQuery' => $alternativeQuery
                    ]
                );
            case self::IMPROVED_QUERY:
                return $this->translator->trans(
                    'Findologic::Template.improvedQuery',
                    [
                        'originalQuery' => $originalQuery,
                        'alternativeQuery' => $alternativeQuery
                    ]
                );
            case self::DID_YOU_MEAN_QUERY:
                return $this->translator->trans(
                    'Findologic::Template.didYouMeanQuery',
                    [
                        'originalQuery' => $originalQuery,
                        'alternativeQuery' => $alternativeQuery
                    ]
                );
            default:
                return '';
        }
    }

    /**
     * @return string|null
     */
    public function getLandingPage()
    {
        return $this->getData(self::DATA_LANDING_PAGE);
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
