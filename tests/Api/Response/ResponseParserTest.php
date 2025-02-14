<?php

namespace Findologic\Tests\Api\Response;

use PHPUnit\Framework\TestCase;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Api\Response\Response;
use Plenty\Log\Contracts\LoggerContract;
use Findologic\Api\Response\ResponseParser;
use PHPUnit\Framework\MockObject\MockObject;
use Findologic\Api\Response\Parser\FiltersParser;

/**
 * Class ResponseParserTest
 * @package Findologic\Tests
 */
class ResponseParserTest extends TestCase
{
    /**
     * @var FiltersParser|MockObject
     */
    protected $filterParser;

    /**
     * @var LoggerFactory|MockObject
     */
    protected $loggerFactory;

    /**
     * @var LoggerContract|MockObject
     */
    protected $logger;

    public function setUp(): void
    {
        $this->filterParser = $this->getMockBuilder(FiltersParser::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
    }

    public function testParse()
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        /** @var ResponseParser|MockObject $responseParserMock */
        $responseParserMock = $this->getResponseParserMock(['createResponseObject']);
        $responseParserMock->expects($this->any())->method('createResponseObject')->willReturn($responseMock);

        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();

        $results = $responseParserMock->parse($requestMock, $this->getResponse());
        $this->assertEquals(3, $results->getResultsCount());

        $promotionsData = $results->getData(Response::DATA_PROMOTION);
        $this->assertEquals($promotionsData, [
            'image' => 'http://www.example.com/special-offer.jpg',
            'link' => 'http://www.example.com/special-offer'
        ]);
    }

    public function responseDataProvider()
    {
        $plentyErrorResponse = [
            'error' => true,
            'error_no' => 0,
            'error_msg' => 'Curl error: Could not resolve host: service.findologic.com',
            'error_file' => '/findologic/http_request2/HTTP/Request2/Adapter/Curl.php',
            'error_line' => 155
        ];

        return [
            'Plentymarkets error response' => [
                'response' => $plentyErrorResponse,
                'errorMessage' =>
                    'Still invalid response after 2 retries. Using Plentymarkets SDK results without Findologic.',
                'errorContext' => ['response' => $plentyErrorResponse],
            ],
            'Empty response' => [
                'response' => '',
                'errorMessage' =>
                    'Still invalid response after 2 retries. Using Plentymarkets SDK results without Findologic.',
                'errorContext' => ['response' => ''],
            ],
            'Invalid JSON response' => [
                'response' => 'invalid-json',
                'errorMessage' => 'Parsing JSON failed',
                'errorContext' => ['jsonString' => 'invalid-json'],
            ],
        ];
    }

    /**
     * @dataProvider responseDataProvider
     *
     * @param string|array $response
     * @param string $errorMessage
     * @param array $errorContext
     */
    public function testHandleInvalidResponse($response, string $errorMessage, array $errorContext)
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        /** @var ResponseParser|MockObject $responseParserMock */
        $responseParserMock = $this->getResponseParserMock(['createResponseObject']);
        $responseParserMock->expects($this->any())->method('createResponseObject')->willReturn($responseMock);

        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage, $errorContext);

        $responseParserResult = $responseParserMock->parse($requestMock, $response);
        $this->assertEquals([], $responseParserResult->getData());
    }

    /**
     * @dataProvider queryInfoMessageProvider
     *
     * @param array $requestParams
     * @param string $response
     * @param array|null $expectedResult
     */
    public function testQueryInfoMessageParsing(array $requestParams, string $response, $expectedResult)
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        /** @var ResponseParser|MockObject $responseParserMock */
        $responseParserMock = $this->getResponseParserMock(['createResponseObject', 'handleLandingPage']);
        $responseParserMock->expects($this->any())->method('createResponseObject')->willReturn($responseMock);

        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $requestMock->expects($this->once())->method('all')->willReturn($requestParams);

        $results = $responseParserMock->parse($requestMock, $response);

        $this->assertEquals($results->getData(Response::DATA_QUERY_INFO_MESSAGE), $expectedResult);
    }

    public function queryInfoMessageProvider()
    {
        return [
            'No Smart Did-You-Mean data provided' => [
                [],
                '{
                    "request": {
                        "query": "Test",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null,
                ]
            ],
            'Did-You-Mean query present' => [
                [],
                '{
                    "request": {
                        "query": "Test",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym",
                            "didYouMeanQuery": "TestDidYouMeanQuery"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => 'TestDidYouMeanQuery',
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ]
            ],
            'Improved query present' => [
                [],
                '{
                    "request": {
                        "query": "OriginalTest",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym",
                            "improvedQuery": "Test"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => 'OriginalTest',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => 'improved',
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ]
            ],
            'Corrected query present' => [
                [],
                '{
                    "request": {
                        "query": "OriginalTest",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym",
                            "correctedQuery": "Test"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => 'OriginalTest',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => 'corrected',
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ]
            ],
            'Uses shopping guide' => [
                [
                    'attrib' => [
                        'wizard' => [
                            'TestShoppingGuide'
                        ]
                    ]
                ],
                '{
                    "request": {
                        "query": "Test",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => 'TestShoppingGuide'
                ]
            ],
            'Category selected' => [
                [
                    'attrib' => [
                        'cat' => [
                            'TestCat'
                        ]
                    ]
                ],
                '{
                    "request": {
                        "query": "Test",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestCat',
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ]
            ],
            'Child category selected' => [
                [
                    'attrib' => [
                        'cat' => [
                            'TestCat_TestChildCat'
                        ]
                    ]
                ],
                '{
                    "request": {
                        "query": "Test",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestChildCat',
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ]
            ],
            'Vendor selected' => [
                [
                    'attrib' => [
                        'vendor' => [
                            'TestVendor'
                        ]
                    ]
                ],
                '{
                    "request": {
                        "query": "Test",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => 'TestVendor',
                    'shoppingGuide' => null
                ]
            ],
            'Category and vendor selected' => [
                [
                    'attrib' => [
                        'cat' => [
                            'TestCat_TestChildCat'
                        ],
                        'vendor' => [
                            'TestVendor'
                        ]
                    ]
                ],
                '{
                    "request": {
                        "query": "Test",
                        "first": 0,
                        "count": 25,
                        "usergroup": null,
                        "order": {
                            "field": "salesfrequency",
                            "relevanceBased": true,
                            "direction": "DESC"
                        }
                    },
                    "result": {
                        "metadata": {
                            "landingpage": null,
                            "promotion": null,
                            "searchConcept": null,
                            "effectiveQuery": "Test",
                            "totalResults": 0,
                            "currencySymbol": "\u20ac"
                        },
                        "items": [],
                        "variant": {
                            "name": "sdym"
                        },
                        "filters": {
                            "main": [],
                            "other": []
                        }
                    }
                }',
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestChildCat',
                    'selectedVendorName' => 'TestVendor',
                    'shoppingGuide' => null
                ]
            ]
        ];
    }

    /**
     * @param array|null $methods
     * @return ResponseParser|MockObject
     */
    protected function getResponseParserMock($methods = null)
    {
        $responseParserMock = $this->getMockBuilder(ResponseParser::class)
            ->setConstructorArgs([
                'filtersParser' => $this->filterParser,
                'loggerFactory' => $this->loggerFactory
            ])
            ->setMethods($methods);

        return $responseParserMock->getMock();
    }

    /**
     * @return string
     */
    protected function getResponse()
    {
        return '{
            "request": {
                "query": "Test",
                "first": 0,
                "count": 10
            },
            "result": {
                "metadata": {
                    "landingpage": 
                    {
                        "url" : "http://www.example.com/imprint"
                    },
                    "searchConcept": null,
                    "effectiveQuery": "Test",
                    "totalResults": 3,
                    "currencySymbol": "\u20ac",
                    "promotion": {
                        "imageUrl": "http://www.example.com/special-offer.jpg",
                        "url": "http://www.example.com/special-offer"
                    }
                },
                "items": [
                    {
                        "id": "17",
                        "score": "5.5451774597168",
                        "_direct": "0"
                    },
                    {
                        "id": "18",
                        "score": "5.5451774597168",
                        "_direct": "0"
                    },
                    {
                        "id": "19",
                        "score": "5.5451774597168",
                        "_direct": "0"
                    }
                ],
                "variant": {
                    "name": "sdym"
                },
                "filters": {
                    "main": [
                        {
                            "name": "cat",
                            "selectMode": "multiple",
                            "values": [
                                {
                                    "value": "Untergruppe",
                                    "weight": "0.863121",
                                    "frequency": "5",
                                    "image": "http://www.example.com/images/Untergruppe.jpg",
                                    "values": [
                                        {
                                            "value": "Unteruntergruppe",
                                            "weight": "0.985228",
                                            "frequency": "4",
                                            "values": [
                                                {
                                                    "value": "Unteruntergruppe 1",
                                                    "weight": "0.985228",
                                                    "frequency": "4",
                                                    "values": [
                                                        {
                                                            "value": "Unteruntergruppe 2",
                                                            "weight": "0.985228",
                                                            "frequency": "4"
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            "name": "vendor",
                            "selectMode": "multiple",
                            "values": [
                                {
                                    "value": "Exclusive Leather",
                                    "weight": "0.68965518474579",
                                    "frequency": "10"
                                },
                                {
                                    "value": "HUNDE design",
                                    "weight": "0.68965518474579",
                                    "frequency": "19"
                                }
                            ]
                        },
                        {
                            "name": "price",
                            "displayName": "Preis",
                            "selectMode": "single",
                            "type": "range-slider",
                            "selectedRange": {
                                "min": "59",
                                "max": "2300"
                            },
                            "totalRange": {
                                "min": "59",
                                "max": "2300"
                            },
                            "stepSize": "0.1",
                            "unit": "€",
                            "values": [
                                    {
                                        "weight": "0.5517241358757",
                                        "value": {
                                            "min": "59",
                                            "max": "139"
                                        }
                                    },
                                    {
                                        "weight": "0.5517241358757",
                                        "value": {
                                            "min": "146.37",
                                            "max": "250"
                                        }
                                    },
                                    {
                                        "weight": "0.5517241358757",
                                        "value": {
                                            "min": "269",
                                            "max": "730"
                                        }
                                    },
                                    {
                                        "weight": "0.34482759237289",
                                        "value": {
                                            "min": "740",
                                            "max": "2300"
                                        }
                                    }
                                ]
                        }
                    ],
                    "other": [
                        {
                            "name": "Farbe",
                            "displayName": "Farbe",
                            "selectMode": "multiselect",
                            "selectedItems": "0",
                            "type": "color",
                            "values": [
                                    {
                                        "value": "lila",
                                        "weight": "0.068965516984463",
                                        "color": "#BA55D3"
                                    },
                                    {
                                        "value": "rot",
                                        "weight": "0.068965516984463",
                                        "color": "#FF0000"
                                    },
                                    {
                                        "value": "schwarz",
                                        "weight": "0.068965516984463",
                                        "color": "#000000"
                                    },
                                    {
                                        "value": "weiß",
                                        "weight": "0.068965516984463",
                                        "color": "#FFFFFF"
                                    }
                                ]
                        }
                    ]
                }
            }
        }';
    }
}
