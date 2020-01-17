<?php

namespace Findologic\Constants;

/**
 * Class Plugin
 * @package Findologic\Constants
 */
class Plugin
{
    const PLUGIN_NAMESPACE = 'Findologic';
    const PLUGIN_IDENTIFIER = 'findologic-plugin-api';
    const PLUGIN_VERSION = '2.4.1';

    const PLENTY_PARAMETER_PAGINATION_ITEMS_PER_PAGE = 'items';

    const API_OUTPUT_ADAPTER = 'XML_2.1';
    const API_PARAMETER_ATTRIBUTES = 'attrib';
    const API_PARAMETER_PROPERTIES = 'properties';
    const API_PARAMETER_SORT_ORDER = 'order';
    const API_PARAMETER_PAGINATION_ITEMS_PER_PAGE = 'count';
    const API_PARAMETER_PAGINATION_START = 'first';

    const API_PROPERTY_VARIATION_ID = 'variation_id';

    const API_CONFIGURATION_KEY_CONNECTION_TIME_OUT = 'connection_time_out';
    const API_CONFIGURATION_KEY_TIME_OUT = 'time_out';

    const API_ALIVE_RESPONSE_BODY = 'alive';

    const API_SORT_ORDER_AVAILABLE_OPTIONS = [
        'sorting.price.avg_asc',
        'sorting.price.avg_desc',
        'texts.name1_asc',
        'texts.name1_desc',
        'variation.createdAt_desc',
        'variation.createdAt_asc',
        'item.score',
        'variation.position_asc',
        'variation.position_desc'
    ];

    const CONFIG_SHOPKEY = 'Findologic.shopkey';
    const CONFIG_NAVIGATION_ENABLED = 'Findologic.nav_enabled';
    const CONFIG_SEARCH_RESULT_CONTAINER = 'Findologic.search_result_container';
    const CONFIG_NAVIGATION_CONTAINER = 'Findologic.navigation_container';
    const CONFIG_FILTERS_STYLING_CSS_ENABLED = 'Findologic.filters_styling_css_enabled';
    const CONFIG_LOAD_NO_UI_SLIDER_STYLES_ENABLED = 'Findologic.load_no_ui_slider_styles_enabled';

    const FILTER_TYPE_RANGE_SLIDER = 'range-slider';
    const FILTER_TYPE_COLOR = 'color';
    const FILTER_TYPE_IMAGE = 'image';
}