<?php

namespace Findologic\PluginPlentymarketsApi\Api\Request;

use Findologic\PluginPlentymarketsApi\Constants\Plugin;

/**
 * Class Request
 * @package Findologic\Api\Request
 */
class Request
{
    /**
     * Request url
     *
     * @var string
     */
    protected $url;

    /**
     * Request headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Request parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        $url = $this->getUrl();
        $query = '';

        if (count($this->getParams()) >= 1) {
            $query = '?' . http_build_query($this->getParams());
        }

        return $url . $query;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Request
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Request
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return Request
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttributeParam($key, $value)
    {
        if (!isset($this->params[Plugin::API_PARAMETER_ATTRIBUTES])) {
            $this->params[Plugin::API_PARAMETER_ATTRIBUTES] = [];
        }

        $this->params[Plugin::API_PARAMETER_ATTRIBUTES][$key] = $value;

        return $this;
    }
}