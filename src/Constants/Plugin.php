<?php

namespace Findologic\Constants;

/**
 * Class Plugin
 * @package FindologicCeres\Constants
 */
class Plugin
{
    const PLUGIN_VERSION = '0.0.7';
    const PLUGIN_NAMESPACE = 'Findologic';
    const PLUGIN_IDENTIFIER = 'findologic-plugin-api';

    const PLENTY_PARAMETER_SORT_ORDER = 'sorting';
    const PLENTY_PARAMETER_PAGINATION_ITEMS_PER_PAGE = 'items';
    const PLENTY_PARAMETER_PAGINATION_START = 'first';

    const API_OUTPUT_ADAPTER = 'XML_2.0';
    const API_PARAMETER_ATTRIBUTES = 'attrib';
    const API_PARAMETER_PROPERTIES = 'properties';
    const API_PARAMETER_SORT_ORDER = 'order';
    const API_PARAMETER_PAGINATION_ITEMS_PER_PAGE = 'count';
    const API_PARAMETER_PAGINATION_START = 'first';

    const API_PROPERTY_VARIATION_ID = 'variation_id';

    const API_CONFIGURATION_KEY_CONNECTION_TIME_OUT = 'connection_time_out';
    const API_CONFIGURATION_KEY_TIME_OUT = 'time_out';

    const API_ALIVE_RESPONSE_BODY = 'alive';

    // item.score is missing on purpose since it is the default option and shouldn't actually be sent in the request.
    const API_SORT_ORDER_AVAILABLE_OPTIONS = [
        'sorting.price.avg_asc',
        'sorting.price.avg_desc',
        'texts.name1_asc',
        'texts.name1_desc',
        'default.recommended_sorting',
        'variation.createdAt_desc',
        'variation.createdAt_asc'
    ];

    const CONFIG_SHOPKEY = 'Findologic.shopkey';
    const CONFIG_NAVIGATION_ENABLED = 'Findologic.nav_enabled';

    const FILTER_TYPE_RANGE_SLIDER = 'range-slider';
    const FILTER_TYPE_COLOR = 'color';
}