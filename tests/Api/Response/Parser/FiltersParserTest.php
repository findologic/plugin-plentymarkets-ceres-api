<?php

namespace Findologic\Tests\Api\Response\Parser;

use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Api\Response\Response;
use Findologic\Api\Services\Image;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Plenty\Plugin\ConfigRepository;
use SimpleXMLElement;

/**
 * Class FiltersParserTest
 * @package Findologic\Tests\Api\Response\Parser
 */
class FiltersParserTest extends TestCase
{
    /**
     * @var Image|MockObject
     */
    protected $imageService;

    /**
     * @var LibraryCallContract|MockObject
     */
    protected $libraryCallContract;

    /**
     * @var ConfigRepository|MockObject
     */
    protected $configRepository;

    public function setUp(): void
    {
        $this->libraryCallContract = $this->getMockBuilder(LibraryCallContract::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->imageService = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    /**
     * @dataProvider parseDataProvider
     *
     * @param string $response
     * @param array $expectedResult
     */
    public function testParse($response, array $expectedResults)
    {
        /** @var FiltersParser|MockObject $filtersParserMock */
        $filtersParserMock = $this->getFiltersParserMock();

        $results = $filtersParserMock->parse(json_decode($response, 1));
        if (!$response)
            $this->assertEmpty($results);

        $this->assertEquals($expectedResults, $results);
    }

    /**
     * @param array $methods
     * @return MockObject
     */
    protected function getFiltersParserMock($methods = [])
    {
        $methods[] = 'createResponseObject';

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $filtersParserMock = $this->getMockBuilder(FiltersParser::class)
            ->setConstructorArgs([
                'libraryCallContract' => $this->libraryCallContract,
                'configRepository' => $this->configRepository
            ])
            ->setMethods($methods)->getMock();
        $filtersParserMock->expects($this->any())->method('createResponseObject')->willReturn($responseMock);

        return $filtersParserMock;
    }

    public function parseDataProvider()
    {
        return [
            'Filters and filter values are set in response' => [
                '{
                    "main": [
                        {
                            "name": "cat",
                            "displayName": "Kategorie",
                            "selectMode": "single",
                            "type": "select",
                            "values": [
                                {
                                    "value": "Wohnzimmer",
                                    "weight": "0.30303025245667",
                                    "frequency": "28",
                                    "values": [
                                        {
                                            "value": "Sessel & Hocker",
                                            "weight": "0.96969699859619",
                                            "frequency": "17"
                                        },
                                        {
                                            "value": "Sofas",
                                            "weight": "0.66666668653488",
                                            "frequency": "11"
                                        }
                                    ]
                                },
                                {
                                    "value": "Arbeitszimmer & Büro",
                                    "weight": "0.36363637447357",
                                    "frequency": "6",
                                    "values": [
                                        {
                                            "value": "Bürostühle",
                                            "weight": "0.36363637447357",
                                            "frequency": "6"
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            "name": "vendor",
                            "selectMode": "multiple",
                            "type": "image",
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
                                },
                                {
                                    "value": "A & C Design",
                                    "weight": "0.72727274894714",
                                    "frequency": "21",
                                    "image": "/vendor/a_amp_c_design.jpg"
                                },
                                {
                                    "value": "H Manufacturer",
                                    "weight": "0.52727274894714",
                                    "frequency": "25",
                                    "image": "https://test.com/vendor/a_amp_c_design.jpg"
                                }
                            ]
                        },
                        {
                            "name": "price",
                            "displayName": "Preis",
                            "selectMode": "single",
                            "type": "range-slider",
                            "selectedRange": {
                                "min": 59,
                                "max": 2300
                            },
                            "totalRange": {
                                "min": 59,
                                "max": 2300
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
                            "name": "price-text",
                            "displayName": "Preis",
                            "selectMode": "single",
                            "type": "text",
                            "values": [
                                {
                                    "items" : [],
                                    "value" : "",
                                    "position" : "item",
                                    "count" : "",
                                    "selected" : false
                                }
                            ]
                        },
                        {
                            "name": "color",
                            "displayName": "Farbe",
                            "selectMode": "multiple",
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
                }',
                [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [
                                    [
                                        'name' => 'Sessel & Hocker',
                                        'position' => 'item',
                                        'count' => '17',
                                        'id' => 2,
                                        'selected' => false,
                                        'items' => []
                                    ],
                                    [
                                        'items' => [],
                                        'name' => 'Sofas',
                                        'position' => 'item',
                                        'count' => '11',
                                        'selected' => false,
                                        'id' => 3
                                    ]
                                ],
                                'name' => 'Wohnzimmer',
                                'position' => 'item',
                                'count' => "28",
                                'id' => 1,
                                'selected' => false,
                            ],
                            [
                                'items' => [
                                    [
                                        'items' => [],
                                        'name' => 'Bürostühle',
                                        'position' => 'item',
                                        'count' => '6',
                                        'selected' => false,
                                        'id' => 5
                                    ]
                                ],
                                'name' => 'Arbeitszimmer & Büro',
                                'position' => 'item',
                                'count' => '6',
                                'selected' => false,
                                'id' => 4
                            ]
                        ]
                    ],
                    [
                        'id' => 'vendor',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'image',
                        'cssClass' => '',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => '10',
                                'selected' => false,
                                'id' => 6
                            ],
                            [
                                'items' => [],
                                'name' => 'HUNDE design',
                                'position' => 'item',
                                'count' => '19',
                                'selected' => false,
                                'id' => 7
                            ],
                            [
                                'items' => [],
                                'name' => 'A & C Design',
                                'position' => 'item',
                                'count' => '21',
                                'selected' => false,
                                'imageUrl' => '/vendor/a_amp_c_design.jpg',
                                'id' => 8
                            ],
                            [
                                'items' => [],
                                'name' => 'H Manufacturer',
                                'position' => 'item',
                                'count' => '25',
                                'selected' => false,
                                'id' => 9,
                                'imageUrl' => 'https://test.com/vendor/a_amp_c_design.jpg'
                            ]
                        ]
                    ],
                    [
                        'id' => 'price',
                        'name' => 'Preis',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'range-slider',
                        'cssClass' => '',
                        'unit' => '€',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'minValue' => 59,
                        'maxValue' => 2300,
                        'step' => 0.0,
                        'useNoUISliderCSS' => false,
                        'values' => [
                            [
                                'items' => [],
                                'name' => '59 - 139',
                                'position' => 'item',
                                'count' => '',
                                'selected' => false,
                                'id' => 10
                            ],
                            [
                                'items' => [],
                                'name' => '146.37 - 250',
                                'position' => 'item',
                                'count' => '',
                                'selected' => false,
                                'id' => 11
                            ],
                            [
                                'items' => [],
                                'name' => '269 - 730',
                                'position' => 'item',
                                'count' => '',
                                'selected' => false,
                                'id' => 12
                            ],
                            [
                                'items' => [],
                                'name' => '740 - 2300',
                                'position' => 'item',
                                'count' => '',
                                'selected' => false,
                                'id' => 13
                            ]
                        ]
                    ],
                    [
                        'id' => 'price-text',
                        'name' => 'Preis',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'text',
                        'cssClass' => '',
                        'isMain' => false,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => '',
                                'position' => 'item',
                                'count' => '',
                                'selected' => false,
                                'id' => 14
                            ]
                        ]
                    ],
                    [
                        'id' => 'color',
                        'name' => 'Farbe',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'color',
                        'cssClass' => '',
                        'isMain' => false,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'lila',
                                'position' => 'item',
                                'count' => '',
                                'id' => 15,
                                'hexValue' => '#BA55D3',
                                'selected' => false,
                            ],
                            [
                                'items' => [],
                                'name' => 'rot',
                                'position' => 'item',
                                'count' => '',
                                'id' => 16,
                                'hexValue' => '#FF0000',
                                'selected' => false,
                            ],
                            [
                                'items' => [],
                                'name' => 'schwarz',
                                'position' => 'item',
                                'count' => '',
                                'id' => 17,
                                'hexValue' => '#000000',
                                'selected' => false,
                            ],
                            [
                                'items' => [],
                                'name' => 'weiß',
                                'position' => 'item',
                                'count' => '',
                                'id' => 18,
                                'hexValue' => '#FFFFFF',
                                'selected' => false
                            ]
                        ]
                    ]
                ]
            ],
            'No filters are returned in response' => [
                null,
                []
            ],
            'Css class is not set in response' => [
                '{
                    "main": [
                        {
                            "name": "vendor",
                            "selectMode": "multiple",
                            "type": "image",
                            "values": [
                                {
                                    "value": "Exclusive Leather",
                                    "weight": "0.68965518474579",
                                    "frequency": "10"
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'vendor',
                        'cssClass' => '',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'image',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'Css class exists in response, but has no value' => [
                '{
                    "main": [
                        {
                            "cssClass": "",
                            "name": "vendor",
                            "selectMode": "multiple",
                            "type": "image",
                            "values": [
                                {
                                    "value": "Exclusive Leather",
                                    "weight": "0.68965518474579",
                                    "frequency": "10"
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'vendor',
                        'cssClass' => '',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'image',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'Css class exists in response and has a value' => [
                '{
                    "main": [
                        {
                            "cssClass": "test-css-class",
                            "name": "vendor",
                            "selectMode": "multiple",
                            "type": "image",
                            "values": [
                                {
                                    "value": "Exclusive Leather",
                                    "weight": "0.68965518474579",
                                    "frequency": "10"
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'vendor',
                        'cssClass' => 'test-css-class',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'image',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'Filter values are marked as selected' => [
                '{
                    "main": [
                        {
                            "name": "vendor",
                            "selectMode": "multiple",
                            "type": "image",
                            "values": [
                                {
                                    "value": "Exclusive Leather",
                                    "weight": "0.68965518474579",
                                    "frequency": "10"
                                },
                                {
                                    "value": "HUNDE design",
                                    "weight": "0.68965518474579",
                                    "frequency": "19",
                                    "selected": true
                                },
                                {
                                    "value": "A & C Design",
                                    "weight": "0.72727274894714",
                                    "frequency": "21",
                                    "image": "/vendor/a_amp_c_design.jpg",
                                    "selected": false
                                },
                                {
                                    "value": "H Manufacturer",
                                    "weight": "0.52727274894714",
                                    "frequency": "25",
                                    "image": "https://test.com/vendor/a_amp_c_design.jpg"
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'vendor',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'image',
                        'cssClass' => '',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => '10',
                                'selected' => false,
                                'id' => 1
                            ],
                            [
                                'items' => [],
                                'name' => 'HUNDE design',
                                'position' => 'item',
                                'count' => '19',
                                'selected' => true,
                                'id' => 2
                            ],
                            [
                                'items' => [],
                                'name' => 'A & C Design',
                                'position' => 'item',
                                'count' => '21',
                                'selected' => false,
                                "imageUrl" => "/vendor/a_amp_c_design.jpg",
                                'id' => 3
                            ],
                            [
                                'items' => [],
                                'name' => 'H Manufacturer',
                                'position' => 'item',
                                'count' => '25',
                                'selected' => false,
                                'id' => 4,
                                'imageUrl' => 'https://test.com/vendor/a_amp_c_design.jpg'
                            ]
                        ]
                    ],
                ]
            ],
            'itemCount is not set in the response' => [
                '{
                    "main": [
                        {
                            "cssClass": "test-css-class",
                            "name": "vendor",
                            "selectMode": "multiple",
                            "type": "image",
                            "values": [
                                {
                                    "value": "Exclusive Leather",
                                    "weight": "0.68965518474579",
                                    "frequency": "10"
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'vendor',
                        'cssClass' => 'test-css-class',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'image',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'itemCount is set in the response' => [
                '{
                    "main": [
                        {
                            "pinnedFilterValueCount": 42,
                            "cssClass": "test-css-class",
                            "name": "vendor",
                            "selectMode": "multiple",
                            "type": "image",
                            "values": [
                                {
                                    "value": "Exclusive Leather",
                                    "weight": "0.68965518474579",
                                    "frequency": "10"
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'vendor',
                        'cssClass' => 'test-css-class',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'image',
                        'isMain' => true,
                        'itemCount' => 42,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'Child category is selected' => [
                '{
                    "main": [
                        {
                            "name": "cat",
                            "displayName": "Kategorie",
                            "selectMode": "single",
                            "type": "select",
                            "values": [
                                {
                                    "value": "Wohnzimmer",
                                    "weight": "0.30303025245667",
                                    "frequency": "28",
                                    "values": [
                                        {
                                            "value": "Sessel & Hocker",
                                            "weight": "0.96969699859619",
                                            "frequency": "17"
                                        },
                                        {
                                            "value": "Sofas",
                                            "weight": "0.66666668653488",
                                            "frequency": "11",
                                            "selected": true
                                        }
                                    ]
                                },
                                {
                                    "value": "Arbeitszimmer & Büro",
                                    "weight": "0.36363637447357",
                                    "frequency": "6",
                                    "values": [
                                        {
                                            "value": "Bürostühle",
                                            "weight": "0.36363637447357",
                                            "frequency": "6"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [
                                    [
                                        'name' => 'Sessel & Hocker',
                                        'position' => 'item',
                                        'count' => '17',
                                        'id' => 2,
                                        'selected' => false,
                                        'items' => []
                                    ],
                                    [
                                        'items' => [],
                                        'name' => 'Sofas',
                                        'position' => 'item',
                                        'count' => '11',
                                        'selected' => true,
                                        'id' => 3
                                    ]
                                ],
                                'name' => 'Wohnzimmer',
                                'position' => 'item',
                                'count' => "28",
                                'id' => 1,
                                'selected' => true,
                            ],
                            [
                                'items' => [
                                    [
                                        'items' => [],
                                        'name' => 'Bürostühle',
                                        'position' => 'item',
                                        'count' => '6',
                                        'selected' => false,
                                        'id' => 5
                                    ]
                                ],
                                'name' => 'Arbeitszimmer & Büro',
                                'position' => 'item',
                                'count' => '6',
                                'selected' => false,
                                'id' => 4
                            ]
                        ]
                    ],
                ]
            ],
            'Parent category is selected' => [
                '{
                    "main": [
                        {
                            "name": "cat",
                            "displayName": "Kategorie",
                            "selectMode": "single",
                            "type": "select",
                            "values": [
                                {
                                    "value": "Wohnzimmer",
                                    "weight": "0.30303025245667",
                                    "frequency": "28",
                                    "values": [
                                        {
                                            "value": "Sessel & Hocker",
                                            "weight": "0.96969699859619",
                                            "frequency": "17"
                                        },
                                        {
                                            "value": "Sofas",
                                            "weight": "0.66666668653488",
                                            "frequency": "11"
                                        }
                                    ],
                                    "selected": true
                                },
                                {
                                    "value": "Arbeitszimmer & Büro",
                                    "weight": "0.36363637447357",
                                    "frequency": "6",
                                    "values": [
                                        {
                                            "value": "Bürostühle",
                                            "weight": "0.36363637447357",
                                            "frequency": "6"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [
                                    [
                                        'name' => 'Sessel & Hocker',
                                        'position' => 'item',
                                        'count' => '17',
                                        'id' => 2,
                                        'selected' => false,
                                        'items' => []
                                    ],
                                    [
                                        'items' => [],
                                        'name' => 'Sofas',
                                        'position' => 'item',
                                        'count' => '11',
                                        'selected' => false,
                                        'id' => 3
                                    ]
                                ],
                                'name' => 'Wohnzimmer',
                                'position' => 'item',
                                'count' => "28",
                                'id' => 1,
                                'selected' => true,
                            ],
                            [
                                'items' => [
                                    [
                                        'items' => [],
                                        'name' => 'Bürostühle',
                                        'position' => 'item',
                                        'count' => '6',
                                        'selected' => false,
                                        'id' => 5
                                    ]
                                ],
                                'name' => 'Arbeitszimmer & Büro',
                                'position' => 'item',
                                'count' => '6',
                                'selected' => false,
                                'id' => 4
                            ]
                        ]
                    ],
                ]
            ],
            'noAvailableFiltersText is not set in the response' => [
                '{
                    "main": [
                        {
                            "name": "cat",
                            "displayName": "Kategorie",
                            "selectMode": "single",
                            "type": "select",
                            "values": [
                                {
                                    "value": "Wohnzimmer",
                                    "weight": "0.30303025245667",
                                    "frequency": "28",
                                    "values": [
                                        {
                                            "value": "Sessel & Hocker",
                                            "weight": "0.96969699859619",
                                            "frequency": "17"
                                        },
                                        {
                                            "value": "Sofas",
                                            "weight": "0.66666668653488",
                                            "frequency": "11"
                                        }
                                    ]
                                },
                                {
                                    "value": "Arbeitszimmer & Büro",
                                    "weight": "0.36363637447357",
                                    "frequency": "6",
                                    "values": [
                                        {
                                            "value": "Bürostühle",
                                            "weight": "0.36363637447357",
                                            "frequency": "6"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [
                                    [
                                        'name' => 'Sessel & Hocker',
                                        'position' => 'item',
                                        'count' => '17',
                                        'id' => 2,
                                        'selected' => false,
                                        'items' => []
                                    ],
                                    [
                                        'items' => [],
                                        'name' => 'Sofas',
                                        'position' => 'item',
                                        'count' => '11',
                                        'selected' => false,
                                        'id' => 3
                                    ]
                                ],
                                'name' => 'Wohnzimmer',
                                'position' => 'item',
                                'count' => "28",
                                'id' => 1,
                                'selected' => false,
                            ],
                            [
                                'items' => [
                                    [
                                        'items' => [],
                                        'name' => 'Bürostühle',
                                        'position' => 'item',
                                        'count' => '6',
                                        'selected' => false,
                                        'id' => 5
                                    ]
                                ],
                                'name' => 'Arbeitszimmer & Büro',
                                'position' => 'item',
                                'count' => '6',
                                'selected' => false,
                                'id' => 4
                            ]
                        ]
                    ]
                ]
            ],
            'noAvailableFiltersText is set in the response' => [
                '{
                    "main": [
                        {
                            "pinnedFilterValueCount": 6,
                            "noAvailableFiltersText": "Nothing left to show",
                            "name": "cat",
                            "displayName": "Kategorie",
                            "selectMode": "single",
                            "type": "select",
                            "values": [
                                {
                                    "value": "Sofas",
                                    "selected": true
                                }
                            ]
                        }
                    ]
                }',
                [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => true,
                        'itemCount' => 6,
                        'noAvailableFiltersText' => 'Nothing left to show',
                        'values' => [
                            [
                                'name' => 'Sofas',
                                'position' => 'item',
                                'count' => '',
                                'id' => 1,
                                'selected' => true,
                                'items' => []
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider parseForWidgetsDataProvider
     */
    public function testParseForWidgets(array $parsedFilters, array $expectedResult)
    {
        $xml = ['filters' => []];
        /** @var FiltersParser|MockObject $filtersParserMock */
        $filtersParserMock = $this->getFiltersParserMock(['parse']);
        $filtersParserMock->method('parse')->willReturn($parsedFilters);
        $this->assertEquals($expectedResult, $filtersParserMock->parseForWidgets($xml));
    }

    public function parseForWidgetsDataProvider(): array
    {
        return [
            'typesGetMappedCorrectlyIfValuesExist' => [
                [
                    [
                        'id' => 'vendor',
                        'name' => 'Manufacturer',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'label',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => 'A & C Design',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 27,
                                'selected' => false,
                            ]
                        ]
                    ],
                    [
                        'id' => 'price',
                        'name' => 'Price',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'range-slider',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'unit' => '£',
                        'minValue' => 59,
                        'maxValue' => 2300,
                        'step' => 0.1,
                        'useNoUISliderCSS' => false,
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => '59 - 149',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 32,
                                'selected' => false,
                            ]
                        ]
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'Category',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => false,
                        'itemCount' => '6',
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                    0 => [
                                        'items' => [
                                        ],
                                        'name' => 'Armchairs & Stools',
                                        'position' => 'item',
                                        'count' => '19',
                                        'image' => '',
                                        'id' => 47,
                                        'selected' => false,
                                    ]
                                ],
                                'name' => 'Living room',
                                'position' => 'item',
                                'count' => '31',
                                'image' => '',
                                'id' => 46,
                                'selected' => false,
                            ]
                        ]
                    ],
                    [
                        'id' => 'Color',
                        'name' => 'Color',
                        'select' => 'multiselect',
                        'type' => '',
                        'findologicFilterType' => 'color',
                        'isMain' => false,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => 'black',
                                'position' => 'item',
                                'count' => '',
                                'image' => 'https://www.etc-shop.de/layout/findologic/black.png',
                                'id' => 36,
                                'selected' => false,
                                'colorImageUrl' => null,
                                'hexValue' => '#000000',
                            ]
                        ]
                    ],
                    [
                        'id' => 'some_attribute',
                        'name' => 'Some Attribute',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'multiselect',
                        'isMain' => false,
                        'itemCount' => '6',
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => '22220',
                                'position' => 'item',
                                'count' => '18',
                                'image' => '',
                                'id' => 51,
                                'selected' => false,
                            ],
                            1 => [
                                'items' => [
                                ],
                                'name' => '22221',
                                'position' => 'item',
                                'count' => '3',
                                'image' => '',
                                'id' => 52,
                                'selected' => false,
                            ],
                        ],
                    ]
                ],
                [
                    [
                        'id' => 'vendor',
                        'name' => 'Manufacturer',
                        'select' => 'multiple',
                        'type' => 'producer',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => 'A & C Design',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 27,
                                'selected' => false,
                            ]
                        ],
                        'findologicFilterType' => 'label'
                    ],
                    [
                        'id' => 'price',
                        'name' => 'Price',
                        'select' => 'single',
                        'type' => 'price',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'unit' => '£',
                        'minValue' => 59,
                        'maxValue' => 2300,
                        'step' => 0.1,
                        'useNoUISliderCSS' => false,
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => '59 - 149',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 32,
                                'selected' => false,
                            ]
                        ],
                        'findologicFilterType' => 'range-slider'
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'Category',
                        'select' => 'single',
                        'type' => 'category',
                        'isMain' => false,
                        'itemCount' => '6',
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                    0 => [
                                        'items' => [
                                        ],
                                        'name' => 'Armchairs & Stools',
                                        'position' => 'item',
                                        'count' => '19',
                                        'image' => '',
                                        'id' => 47,
                                        'selected' => false,
                                    ]
                                ],
                                'name' => 'Living room',
                                'position' => 'item',
                                'count' => '31',
                                'image' => '',
                                'id' => 46,
                                'selected' => false,
                            ]
                        ],
                        'findologicFilterType' => 'select'
                    ],
                    [
                        'id' => 'Color',
                        'name' => 'Color',
                        'select' => 'multiselect',
                        'type' => 'dynamic',
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => 'black',
                                'position' => 'item',
                                'count' => '',
                                'image' => 'https://www.etc-shop.de/layout/findologic/black.png',
                                'id' => 36,
                                'selected' => false,
                                'colorImageUrl' => null,
                                'hexValue' => '#000000',
                            ]
                        ],
                        'findologicFilterType' => 'color',
                        'isMain' => false
                    ],
                    [
                        'id' => 'some_attribute',
                        'name' => 'Some Attribute',
                        'select' => 'multiple',
                        'type' => 'dynamic',
                        'isMain' => false,
                        'itemCount' => '6',
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => [
                            0 => [
                                'items' => [
                                ],
                                'name' => '22220',
                                'position' => 'item',
                                'count' => '18',
                                'image' => '',
                                'id' => 51,
                                'selected' => false,
                            ],
                            1 => [
                                'items' => [
                                ],
                                'name' => '22221',
                                'position' => 'item',
                                'count' => '3',
                                'image' => '',
                                'id' => 52,
                                'selected' => false,
                            ],
                        ],
                        'findologicFilterType' => 'multiselect'
                    ],
                ]
            ],
            'typesAreLeftUnchangedIfNoValuesExist' => [
                [
                    [
                        'id' => 'vendor',
                        'name' => 'Manufacturer',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'label',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => false
                    ],
                    [
                        'id' => 'price',
                        'name' => 'Price',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'range-slider',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'unit' => '£',
                        'minValue' => 59,
                        'maxValue' => 2300,
                        'step' => 0.1,
                        'useNoUISliderCSS' => false,
                        'values' => null
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'Category',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => false,
                        'itemCount' => '6',
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => []
                    ],
                    [
                        'id' => 'Color',
                        'name' => 'Color',
                        'select' => 'multiselect',
                        'type' => '',
                        'findologicFilterType' => 'color',
                        'isMain' => false,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => ''
                    ]
                ],
                [
                    [
                        'id' => 'vendor',
                        'name' => 'Manufacturer',
                        'select' => 'multiple',
                        'type' => '',
                        'findologicFilterType' => 'label',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => false
                    ],
                    [
                        'id' => 'price',
                        'name' => 'Price',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'range-slider',
                        'isMain' => true,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'unit' => '£',
                        'minValue' => 59,
                        'maxValue' => 2300,
                        'step' => 0.1,
                        'useNoUISliderCSS' => false,
                        'values' => null
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'Category',
                        'select' => 'single',
                        'type' => '',
                        'findologicFilterType' => 'select',
                        'isMain' => false,
                        'itemCount' => '6',
                        'noAvailableFiltersText' => '',
                        'cssClass' => '',
                        'values' => []
                    ],
                    [
                        'id' => 'Color',
                        'name' => 'Color',
                        'select' => 'multiselect',
                        'type' => '',
                        'findologicFilterType' => 'color',
                        'isMain' => false,
                        'itemCount' => 0,
                        'noAvailableFiltersText' => '',
                        'cssClass' => ''
                    ]
                ]
            ]
        ];
    }

    public function parseRangeSliderProvider(): array
    {
        $filterData =
            '{
                "main": [
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
                            "min": 59,
                            "max": 2300
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
                ]
            }';

        return [
            '0.01' => [
                'stepSize' => '0.01',
                'response' => $filterData,
            ],
            '0.1' => [
                'stepSize' => '0.1',
                'response' => $filterData,
            ],
            '1' => [
                'stepSize' => '1',
                'response' => $filterData,
            ]
        ];
    }

    /**
     * @dataProvider parseRangeSliderProvider
     */
    public function testStepConfigurationIsUsed(string $stepSize, string $response): void
    {
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();

        $this->configRepository->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['Findologic.price_range_filter_step_size'], ['Findologic.load_no_ui_slider_styles_enabled'])
            ->willReturnOnConsecutiveCalls($stepSize, true);

        /** @var FiltersParser|MockObject $filtersParserMock */
        $filtersParserMock = $this->getFiltersParserMock();

        $results = $filtersParserMock->parse(json_decode($response, 1));

        $this->assertSame((float) $stepSize, $results[0]['step']);
    }

    public function parseRangeSliderProviderNoUI(): array
    {
        $filterData =
            '{
                "main": [
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
                            "min": 59,
                            "max": 2300
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
                ]
            }';

        return [
            'enabled' => [
                'enabled' => "1",
                'response' => $filterData,
            ],
            'disabled' => [
                'enabled' => "0",
                'response' => $filterData,
            ],
        ];
    }

    /**
     * @dataProvider parseRangeSliderProviderNoUI
     */
    public function testNoUiCSSIsUsed(string $enabled, string $response): void
    {
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();

        $this->configRepository->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['Findologic.price_range_filter_step_size'], ['Findologic.load_no_ui_slider_styles_enabled'])
            ->willReturnOnConsecutiveCalls(0.01, $enabled);

        /** @var FiltersParser|MockObject $filtersParserMock */
        $filtersParserMock = $this->getFiltersParserMock();

        $results = $filtersParserMock->parse(json_decode($response, 1));

        $this->assertSame((bool) $enabled, $results[0]['useNoUISliderCSS']);
    }
}
