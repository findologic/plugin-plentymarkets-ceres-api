<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Api\Request\RequestBuilder;
use PHPUnit\Framework\TestCase;
use FINDOLOGIC\Api\Requests\Request;
use FINDOLOGIC\Api\Config;

require_once __DIR__ . '/../../../resources/lib/findologic_client.php';
require_once __DIR__ . '/SdkRestApi.php';

/**
 * Class RequestTest
 * @package Findologic\Tests\Api\Request
 */
class RequestTest extends TestCase
{
    public function getRequestUrlProvider()
    {
        return [
            [
                'http://test.com/index.php',
                ['query' => 'test', 'attrib' => ['color' => ['red', 'blue']]],
                'https://service.findologic.com/ps/http://test.com/index.php/selector.php?shopurl=http%3A%2F%2Ftest.com%2Findex.php&shopkey=2913E20746E3F762ABFC4AFAFE964609&outputAdapter=JSON_1.0&query=test&properties%5B0%5D=variation_id&attrib%5Bcolor%5D%5B0%5D=red&attrib%5Bcolor%5D%5B1%5D=blue&count=20',
                '2913E20746E3F762ABFC4AFAFE964609'
            ]
        ];
    }

    /**
     * @dataProvider getRequestUrlProvider
     *
     * @param string $url
     * @param array $params
     * @param string $expectedResult
     * @param string $shopkey
     */
    public function testGetRequestUrl(
        string $url,
        array $params,
        string $expectedResult,
        string $shopkey
    ) {
        \SdkRestApi::$params = [
            'shopUrl' => $url,
            'externalSearch' => [
                'searchString' => $params['query'],
                'sorting' => 'ASC',
                'categoryId' => null,
                'itemsPerPage' => 20,
                'page' => 1
            ],
            'parameters' => [
                'attrib' => $params['attrib']
            ]
        ];
        $request = Request::getInstance(RequestBuilder::TYPE_NAVIGATION);

        $request = setDefaultValues($request);
        $config = new Config($shopkey);

        $requestUrl = $request->buildRequestUrl($config);

        $this->assertEquals($expectedResult, $requestUrl);
    }
}
