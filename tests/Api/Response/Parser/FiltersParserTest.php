<?php

namespace Findologic\Tests\Api\Response\Parser;

use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Api\Response\Response;
use Findologic\Api\Services\Image;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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

    public function setUp()
    {
        $this->libraryCallContract = $this->getMockBuilder(LibraryCallContract::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->imageService = $this->getMockBuilder(Image::class)
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
    public function testParse($response, array $expectedResult)
    {
        /** @var FiltersParser|MockObject $filtersParserMock */
        $filtersParserMock = $this->getFiltersParserMock();

        $results = $filtersParserMock->parse(simplexml_load_string($response));

        $this->assertEquals($results, $expectedResult);
    }

    /**
     * @param array $methods
     * @return MockObject
     */
    protected function getFiltersParserMock($methods = [])
    {
        $methods[] = 'createResponseObject';

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $filtersParserMock = $this->getMockBuilder(FiltersParser::class)
            ->setConstructorArgs([
                'libraryCallContract' => $this->libraryCallContract,
                'imageService' => $this->imageService
            ])
            ->setMethods($methods)->getMock();
        $filtersParserMock->expects($this->any())->method('createResponseObject')->willReturn($responseMock);

        return $filtersParserMock;
    }

    public function parseDataProvider()
    {
        return [
            'Filters and filter values are set in response' => [
                '<filters>
                    <main>
                        <filter>
                            <name>cat</name>
                            <display>Kategorie</display>
                            <select>single</select>
                            <type>select</type>
                            <items>
                                <item>
                                    <name>Wohnzimmer</name>
                                    <weight>0.30303025245667</weight>
                                    <frequency>28</frequency>
                                    <items>
                                        <item>
                                            <name>Sessel &amp; Hocker</name>
                                            <weight>0.96969699859619</weight>
                                            <frequency>17</frequency>
                                        </item>
                                        <item>
                                            <name>Sofas</name>
                                            <weight>0.66666668653488</weight>
                                            <frequency>11</frequency>
                                        </item>
                                    </items>
                                </item>
                                <item>
                                    <name>Arbeitszimmer &amp; Büro</name>
                                    <weight>0.36363637447357</weight>
                                    <frequency>6</frequency>
                                    <items>
                                        <item>
                                            <name>Bürostühle</name>
                                            <weight>0.36363637447357</weight>
                                            <frequency>6</frequency>
                                        </item>
                                    </items>
                                </item>
                            </items>
                        </filter>
                        <filter>
                            <name>vendor</name>
                            <select>multiple</select>
                            <type>image</type>
                            <items>
                                <item>
                                    <name>Exclusive Leather</name>
                                    <weight>0.68965518474579</weight>
                                    <frequency>10</frequency>
                                </item>
                                <item>
                                    <name>HUNDE design</name>
                                    <weight>0.68965518474579</weight>
                                    <frequency>19</frequency>
                                </item>
                                <item>
                                    <name>A &amp; C Design</name>
                                    <weight>0.72727274894714</weight>
                                    <frequency>21</frequency>
                                    <image>/vendor/a_amp_c_design.jpg</image>
                                </item>
                                <item>
                                    <name>H Manufacturer</name>
                                    <weight>0.52727274894714</weight>
                                    <frequency>25</frequency>
                                    <image>https://test.com/vendor/a_amp_c_design.jpg</image>
                                </item>
                            </items>
                        </filter>
                        <filter>
                            <name>price</name>
                            <display>Preis</display>
                            <select>single</select>
                            <type>range-slider</type>
                            <attributes>
                                <selectedRange>
                                    <min>59</min>
                                    <max>2300</max>
                                </selectedRange>
                                <totalRange>
                                    <min>59</min>
                                    <max>2300</max>
                                </totalRange>
                                <stepSize>0.1</stepSize>
                                <unit>€</unit>
                            </attributes>
                            <items>
                                <item>
                                    <name>59 - 139</name>
                                    <weight>0.5517241358757</weight>
                                    <parameters>
                                        <min>59</min>
                                        <max>139</max>
                                    </parameters>
                                </item>
                                <item>
                                    <name>146.37 - 250</name>
                                    <weight>0.5517241358757</weight>
                                    <parameters>
                                        <min>146.37</min>
                                        <max>250</max>
                                    </parameters>
                                </item>
                                <item>
                                    <name>269 - 730</name>
                                    <weight>0.5517241358757</weight>
                                    <parameters>
                                        <min>269</min>
                                        <max>730</max>
                                    </parameters>
                                </item>
                                <item>
                                    <name>740 - 2300</name>
                                    <weight>0.34482759237289</weight>
                                    <parameters>
                                        <min>740</min>
                                        <max>2300</max>
                                    </parameters>
                                </item>
                            </items>
                        </filter>
                    </main>
                    <other>
                        <filter>
                            <name>price-text</name>
                            <display>Preis</display>
                            <select>single</select>
                            <type>text</type>
                            <items>
                                <item>
                                </item>
                            </items>
                        </filter>
                        <filter>
                            <name>color</name>
                            <display>Farbe</display>
                            <select>multiselect</select>
                            <selectedItems>0</selectedItems>
                            <type>color</type>
                            <items>
                                <item>
                                    <name>lila</name>
                                    <weight>0.068965516984463</weight>
                                    <color>#BA55D3</color>
                                </item>
                                <item>
                                    <name>rot</name>
                                    <weight>0.068965516984463</weight>
                                    <color>#FF0000</color>
                                </item>
                                <item>
                                    <name>schwarz</name>
                                    <weight>0.068965516984463</weight>
                                    <color>#000000</color>
                                </item>
                                <item>
                                    <name>weiß</name>
                                    <weight>0.068965516984463</weight>
                                    <color>#FFFFFF</color>
                                </item>
                            </items>
                        </filter>
                    </other>
                </filters>', [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => 'select',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [
                                    [
                                        'name' => 'Sessel & Hocker',
                                        'position' => 'item',
                                        'count' => '17',
                                        'image' => '',
                                        'id' => 2,
                                        'selected' => false,
                                        'items' => []
                                    ],
                                    [
                                        'items' => [],
                                        'name' => 'Sofas',
                                        'position' => 'item',
                                        'count' => '11',
                                        'image' => '',
                                        'selected' => false,
                                        'id' => 3
                                    ]
                                ],
                                'name' => 'Wohnzimmer',
                                'position' => 'item',
                                'count' => "28",
                                'image' => '',
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
                                        'image' => '',
                                        'selected' => false,
                                        'id' => 5
                                    ]
                                ],
                                'name' => 'Arbeitszimmer & Büro',
                                'position' => 'item',
                                'count' => '6',
                                'image' => '',
                                'selected' => false,
                                'id' => 4
                            ]
                        ]
                    ],
                    [
                        'id' => 'vendor',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => 'image',
                        'cssClass' => '',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => '10',
                                'image' => '',
                                'selected' => false,
                                'id' => 6
                            ],
                            [
                                'items' => [],
                                'name' => 'HUNDE design',
                                'position' => 'item',
                                'count' => '19',
                                'image' => '',
                                'selected' => false,
                                'id' => 7
                            ],
                            [
                                'items' => [],
                                'name' => 'A & C Design',
                                'position' => 'item',
                                'count' => '21',
                                'image' => '/vendor/a_amp_c_design.jpg',
                                'selected' => false,
                                'id' => 8
                            ],
                            [
                                'items' => [],
                                'name' => 'H Manufacturer',
                                'position' => 'item',
                                'count' => '25',
                                'image' => 'https://test.com/vendor/a_amp_c_design.jpg',
                                'selected' => false,
                                'id' => 9
                            ]
                        ]
                    ],
                    [
                        'id' => 'price',
                        'name' => 'Preis',
                        'select' => 'single',
                        'type' => 'range-slider',
                        'cssClass' => '',
                        'unit' => '€',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'minValue' => '59',
                        'maxValue' => '2300',
                        'step' => '0.1',
                        'values' => [
                            [
                                'items' => [],
                                'name' => '59 - 139',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'selected' => false,
                                'id' => 10
                            ],
                            [
                                'items' => [],
                                'name' => '146.37 - 250',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'selected' => false,
                                'id' => 11
                            ],
                            [
                                'items' => [],
                                'name' => '269 - 730',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'selected' => false,
                                'id' => 12
                            ],
                            [
                                'items' => [],
                                'name' => '740 - 2300',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'selected' => false,
                                'id' => 13
                            ]
                        ]
                    ],
                    [
                        'id' => 'price-text',
                        'name' => 'Preis',
                        'select' => 'single',
                        'type' => 'text',
                        'cssClass' => '',
                        'isMain' => false,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => '',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'selected' => false,
                                'id' => 14
                            ]
                        ]
                    ],
                    [
                        'id' => 'color',
                        'name' => 'Farbe',
                        'select' => 'multiselect',
                        'type' => 'color',
                        'cssClass' => '',
                        'isMain' => false,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'lila',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 15,
                                'hexValue' => '#BA55D3',
                                'selected' => false,
                            ],
                            [
                                'items' => [],
                                'name' => 'rot',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 16,
                                'hexValue' => '#FF0000',
                                'selected' => false,
                            ],
                            [
                                'items' => [],
                                'name' => 'schwarz',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 17,
                                'hexValue' => '#000000',
                                'selected' => false,
                            ],
                            [
                                'items' => [],
                                'name' => 'weiß',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
                                'id' => 18,
                                'hexValue' => '#FFFFFF',
                                'selected' => false
                            ]
                        ]
                    ]
                ]
            ],
            'No filters are returned in response' => [
                null, []
            ],
            'Css class is not set in response' => [
                '<filters>
                    <main>
                        <filter>
                            <name>vendor</name>
                            <select>multiple</select>
                            <type>image</type>
                            <items>
                                <item>
                                    <name>Exclusive Leather</name>
                                    <weight>0.68965518474579</weight>
                                    <frequency>10</frequency>
                                </item>
                            </items>
                        </filter>
                    </main>
                </filters>', [
                    [
                        'id' => 'vendor',
                        'cssClass' => '',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => 'image',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'image' => '',
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'Css class exists in response, but has no value' => [
                '<filters>
                    <main>
                        <filter>
                            <cssClass></cssClass>
                            <name>vendor</name>
                            <select>multiple</select>
                            <type>image</type>
                            <items>
                                <item>
                                    <name>Exclusive Leather</name>
                                    <weight>0.68965518474579</weight>
                                    <frequency>10</frequency>
                                </item>
                            </items>
                        </filter>
                    </main>
                </filters>', [
                    [
                        'id' => 'vendor',
                        'cssClass' => '',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => 'image',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'image' => '',
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'Css class exists in response and has a value' => [
                '<filters>
                    <main>
                        <filter>
                            <cssClass>test-css-class</cssClass>
                            <name>vendor</name>
                            <select>multiple</select>
                            <type>image</type>
                            <items>
                                <item>
                                    <name>Exclusive Leather</name>
                                    <weight>0.68965518474579</weight>
                                    <frequency>10</frequency>
                                </item>
                            </items>
                        </filter>
                    </main>
                </filters>', [
                    [
                        'id' => 'vendor',
                        'cssClass' => 'test-css-class',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => 'image',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => "10",
                                'image' => '',
                                'selected' => false,
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'Filter values are marked as selected' => [
                '<filters>
                    <main>
                        <filter>
                            <name>vendor</name>
                            <select>multiple</select>
                            <type>image</type>
                            <items>
                                <item>
                                    <name>Exclusive Leather</name>
                                    <weight>0.68965518474579</weight>
                                    <frequency>10</frequency>
                                </item>
                                <item selected="1">
                                    <name>HUNDE design</name>
                                    <weight>0.68965518474579</weight>
                                    <frequency>19</frequency>
                                </item>
                                <item selected="0">
                                    <name>A &amp; C Design</name>
                                    <weight>0.72727274894714</weight>
                                    <frequency>21</frequency>
                                    <image>/vendor/a_amp_c_design.jpg</image>
                                </item>
                                <item>
                                    <name>H Manufacturer</name>
                                    <weight>0.52727274894714</weight>
                                    <frequency>25</frequency>
                                    <image>https://test.com/vendor/a_amp_c_design.jpg</image>
                                </item>
                            </items>
                        </filter>
                    </main>
                </filters>', [
                    [
                        'id' => 'vendor',
                        'name' => '',
                        'select' => 'multiple',
                        'type' => 'image',
                        'cssClass' => '',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [],
                                'name' => 'Exclusive Leather',
                                'position' => 'item',
                                'count' => '10',
                                'image' => '',
                                'selected' => false,
                                'id' => 1
                            ],
                            [
                                'items' => [],
                                'name' => 'HUNDE design',
                                'position' => 'item',
                                'count' => '19',
                                'image' => '',
                                'selected' => true,
                                'id' => 2
                            ],
                            [
                                'items' => [],
                                'name' => 'A & C Design',
                                'position' => 'item',
                                'count' => '21',
                                'image' => '/vendor/a_amp_c_design.jpg',
                                'selected' => false,
                                'id' => 3
                            ],
                            [
                                'items' => [],
                                'name' => 'H Manufacturer',
                                'position' => 'item',
                                'count' => '25',
                                'image' => 'https://test.com/vendor/a_amp_c_design.jpg',
                                'selected' => false,
                                'id' => 4
                            ]
                        ]
                    ],
                ]
            ],
            'noAvailableFiltersText is not set in the response' => [
                '<filters>
                    <main>
                        <filter>
                            <name>cat</name>
                            <display>Kategorie</display>
                            <select>single</select>
                            <type>select</type>
                            <items>
                                <item>
                                    <name>Wohnzimmer</name>
                                    <weight>0.30303025245667</weight>
                                    <frequency>28</frequency>
                                    <items>
                                        <item>
                                            <name>Sessel &amp; Hocker</name>
                                            <weight>0.96969699859619</weight>
                                            <frequency>17</frequency>
                                        </item>
                                        <item>
                                            <name>Sofas</name>
                                            <weight>0.66666668653488</weight>
                                            <frequency>11</frequency>
                                        </item>
                                    </items>
                                </item>
                                <item>
                                    <name>Arbeitszimmer &amp; Büro</name>
                                    <weight>0.36363637447357</weight>
                                    <frequency>6</frequency>
                                    <items>
                                        <item>
                                            <name>Bürostühle</name>
                                            <weight>0.36363637447357</weight>
                                            <frequency>6</frequency>
                                        </item>
                                    </items>
                                </item>
                            </items>
                        </filter>
                    </main>
                </filters>', [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => 'select',
                        'isMain' => true,
                        'noAvailableFiltersText' => '',
                        'values' => [
                            [
                                'items' => [
                                    [
                                        'name' => 'Sessel & Hocker',
                                        'position' => 'item',
                                        'count' => '17',
                                        'image' => '',
                                        'id' => 2,
                                        'selected' => false,
                                        'items' => []
                                    ],
                                    [
                                        'items' => [],
                                        'name' => 'Sofas',
                                        'position' => 'item',
                                        'count' => '11',
                                        'image' => '',
                                        'selected' => false,
                                        'id' => 3
                                    ]
                                ],
                                'name' => 'Wohnzimmer',
                                'position' => 'item',
                                'count' => "28",
                                'image' => '',
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
                                        'image' => '',
                                        'selected' => false,
                                        'id' => 5
                                    ]
                                ],
                                'name' => 'Arbeitszimmer & Büro',
                                'position' => 'item',
                                'count' => '6',
                                'image' => '',
                                'selected' => false,
                                'id' => 4
                            ]
                        ]
                    ]
                ]
            ],
            'noAvailableFiltersText is set in the response' => [
                '<filters>
                    <main>
                        <filter>
                            <itemCount>6</itemCount>
                            <noAvailableFiltersText>Nothing left to show</noAvailableFiltersText>
                            <name>cat</name>
                            <display>Kategorie</display>
                            <select>single</select>
                            <type>select</type>
                            <items>
                                <item selected="1">
                                    <name>Sofas</name>
                                </item>
                            </items>
                        </filter>
                    </main>
                </filters>', [
                    [
                        'id' => 'cat',
                        'cssClass' => '',
                        'name' => 'Kategorie',
                        'select' => 'single',
                        'type' => 'select',
                        'isMain' => true,
                        'noAvailableFiltersText' => 'Nothing left to show',
                        'values' => [
                            [
                                'name' => 'Sofas',
                                'position' => 'item',
                                'count' => '',
                                'image' => '',
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
}
