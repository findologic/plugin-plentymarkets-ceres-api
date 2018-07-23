<?php

namespace Findologic\Constants;

/**
 * Class Plugin
 * @package FindologicCeres\Constants
 */
class Plugin
{
    const PLUGIN_NAMESPACE = 'Findologic';
    const PLUGIN_IDENTIFIER = 'findologic-plugin-api';

    const API_OUTPUT_ADAPTER = 'XML_2.0';
    const API_PARAMETER_ATTRIBUTES = 'attrib';
    const API_PARAMETER_PROPERTIES = 'properties';
    const API_PARAMETER_SORT_ORDER = 'order';
    const API_PARAMETER_PAGINATION_ITEMS_PER_PAGE = 'count';
    const API_PARAMETER_PAGINATION_START = 'first';

    const API_PROPERTY_MAIN_VARIATION_ID = 'main_variation_id';

    const API_CONFIGURATION_KEY_CONNECTION_TIME_OUT = 'connection_time_out';
    const API_CONFIGURATION_KEY_TIME_OUT = 'time_out';

    const API_ALIVE_RESPONSE_BODY = 'alive';

    const API_SORT_ORDER_AVAILABLE_OPTIONS = ['price ASC', 'price DESC', 'label ASC', 'label DESC', 'salesfrequency ASC', 'salesfrequency DESC', 'dateadded ASC', 'dateadded DESC'];

    const CONFIG_ENABLED = 'Findologic.enabled';
    const CONFIG_URL = 'Findologic.url';
    const CONFIG_SHOPKEY = 'Findologic.shopkey';
}