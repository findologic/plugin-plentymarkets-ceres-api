<?php

namespace Findologic\PluginPlentymarketsApi\Api\Request;

/**
 * Class RequestBuilder
 * @package Findologic\Api\Request
 */
class RequestBuilder
{
    public function build($request, $searchQuery = null)
    {
        $request = pluginApp(Request::class);

        return $request;
    }
}