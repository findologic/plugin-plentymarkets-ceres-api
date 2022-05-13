<?php

namespace Findologic\Tests\Api\Response;

use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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
            'Invalid XML response' => [
                'response' => 'invalid-xml',
                'errorMessage' => 'Parsing XML failed',
                'errorContext' => ['xmlString' => 'invalid-xml'],
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
                '<?xml version="1.0"?>
                <searchResult>
                    <servers>
                        <frontend>frontend.findologic.com</frontend>
                        <backend>backend.findologic.com</backend>
                    </servers>
                    <query>
                        <limit first="0" count="10"/>
                        <queryString>Test</queryString>
                        <searchedWordCount>1</searchedWordCount>
                        <foundWordCount>1</foundWordCount>
                    </query>
                    <results><count>0</count></results>
                    <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                <searchResult>
                    <servers>
                        <frontend>frontend.findologic.com</frontend>
                        <backend>backend.findologic.com</backend>
                    </servers>
                    <query>
                        <limit first="0" count="10"/>
                        <queryString>Test</queryString>
                        <searchedWordCount>1</searchedWordCount>
                        <foundWordCount>1</foundWordCount>
                        <didYouMeanQuery>TestDidYouMeanQuery</didYouMeanQuery>
                    </query>
                    <results><count>0</count></results>
                    <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                <searchResult>
                    <servers>
                        <frontend>frontend.findologic.com</frontend>
                        <backend>backend.findologic.com</backend>
                    </servers>
                    <query>
                        <limit first="0" count="10"/>
                        <originalQuery>OriginalTest</originalQuery>
                        <queryString type="improved">Test</queryString>
                        <searchedWordCount>1</searchedWordCount>
                        <foundWordCount>1</foundWordCount>
                    </query>
                    <results><count>0</count></results>
                    <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                <searchResult>
                    <servers>
                        <frontend>frontend.findologic.com</frontend>
                        <backend>backend.findologic.com</backend>
                    </servers>
                    <query>
                        <limit first="0" count="10"/>
                        <originalQuery>OriginalTest</originalQuery>
                        <queryString type="corrected">Test</queryString>
                        <searchedWordCount>1</searchedWordCount>
                        <foundWordCount>1</foundWordCount>
                    </query>
                    <results><count>0</count></results>
                    <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                <searchResult>
                    <servers>
                        <frontend>frontend.findologic.com</frontend>
                        <backend>backend.findologic.com</backend>
                    </servers>
                    <query>
                        <limit first="0" count="10"/>
                        <queryString>Test</queryString>
                        <searchedWordCount>1</searchedWordCount>
                        <foundWordCount>1</foundWordCount>
                    </query>
                    <results><count>0</count></results>
                    <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                <searchResult>
                    <servers>
                        <frontend>frontend.findologic.com</frontend>
                        <backend>backend.findologic.com</backend>
                    </servers>
                    <query>
                        <limit first="0" count="10"/>
                        <queryString>Test</queryString>
                        <searchedWordCount>1</searchedWordCount>
                        <foundWordCount>1</foundWordCount>
                    </query>
                    <results><count>0</count></results>
                    <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                    <searchResult>
                        <servers>
                            <frontend>frontend.findologic.com</frontend>
                            <backend>backend.findologic.com</backend>
                        </servers>
                        <query>
                            <limit first="0" count="10"/>
                            <queryString>Test</queryString>
                            <searchedWordCount>1</searchedWordCount>
                            <foundWordCount>1</foundWordCount>
                        </query>
                        <results><count>0</count></results>
                        <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                    <searchResult>
                        <servers>
                            <frontend>frontend.findologic.com</frontend>
                            <backend>backend.findologic.com</backend>
                        </servers>
                        <query>
                            <limit first="0" count="10"/>
                            <queryString>Test</queryString>
                            <searchedWordCount>1</searchedWordCount>
                            <foundWordCount>1</foundWordCount>
                        </query>
                        <results><count>0</count></results>
                        <products/>
                    <filters/>
                </searchResult>',
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
                '<?xml version="1.0"?>
                    <searchResult>
                        <servers>
                            <frontend>frontend.findologic.com</frontend>
                            <backend>backend.findologic.com</backend>
                        </servers>
                        <query>
                            <limit first="0" count="10"/>
                            <queryString>Test</queryString>
                            <searchedWordCount>1</searchedWordCount>
                            <foundWordCount>1</foundWordCount>
                        </query>
                        <results><count>0</count></results>
                        <products/>
                    <filters/>
                </searchResult>',
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
        return '<?xml version="1.0"?>
<searchResult>
    <servers>
        <frontend>frontend.findologic.com</frontend>
        <backend>backend.findologic.com</backend>
    </servers>
    <query>
        <limit first="0" count="10"/>
        <queryString>Test</queryString>
        <searchedWordCount>1</searchedWordCount>
        <foundWordCount>1</foundWordCount>
    </query>
    <landingPage link="http://www.example.com/imprint"/>
    <promotion image="http://www.example.com/special-offer.jpg" link="http://www.example.com/special-offer"/>
    <results>
        <count>3</count>
    </results>
    <products>
        <product id="17" relevance="5.5451774597168" direct="0"/>
        <product id="18" relevance="5.5451774597168" direct="0"/>
        <product id="19" relevance="5.5451774597168" direct="0"/>
    </products>
    <filters>
        <main>
            <filter>
                <name>cat</name>
                <select>multiple</select>
                <items>
                    <item>
                        <name>Untergruppe</name>
                        <weight>0.863121</weight>
                        <frequency>5</frequency>
                        <image>http://www.example.com/images/Untergruppe.jpg</image>
                        <items>
                            <item>
                                <name>Unteruntergruppe</name>
                                <weight>0.985228</weight>
                                <frequency>4</frequency>
                                <items>
                                    <item>
                                        <name>Unteruntergruppe 1</name>
                                        <weight>0.985228</weight>
                                        <frequency>4</frequency>
                                        <items>
                                            <item>
                                                <name>Unteruntergruppe 2</name>
                                                <weight>0.985228</weight>
                                                <frequency>4</frequency>
                                            </item>
                                        </items>
                                    </item>
                                </items>
                            </item>
                        </items>
                    </item>
                </items>
            </filter>
            <filter>
                <name>vendor</name>
                <select>multiple</select>
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
                <name>Farbe</name>
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
    </filters>
</searchResult>';
    }
}
