<?php

namespace Findologic\PluginPlentymarketsApi\Constants;

/**
 * Class Plugin
 * @package Findologic\Constants
 */
class Plugin
{
    const PLUGIN_NAMESPACE = 'Findologic';
    const PLUGIN_IDENTIFIER = 'findologic-plugin-api';

    const API_OUTPUT_ADAPTER = 'XML_2.0';
    const API_PARAMETER_ATTRIBUTES = 'attrib';
    const API_PARAMETER_SORT_ORDER = 'order';
    const API_PARAMETER_PAGINATION_ITEMS_PER_PAGE = 'count';
    const API_PARAMETER_PAGINATION_START = 'first';

    const API_SORT_ORDER_AVAILABLE_OPTIONS = ['price ASC', 'price DESC', 'label ASC', 'label DESC', 'salesfrequency ASC', 'salesfrequency DESC', 'dateadded ASC', 'dateadded DESC'];

    const CONFIG_ENABLED = 'findologic.enabled';
    const CONFIG_URL = 'findologic.url';
    const CONFIG_SHOPKEY = 'findologic.shopkey';
}