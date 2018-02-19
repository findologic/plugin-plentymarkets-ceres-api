<?php

namespace Findologic\PluginPlentymarketsApi\Api\Response;

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

    protected $data = [];

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
    public function getData($key, $default = null)
    {
        //TODO: maybe check if data for provided key is empty
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
}