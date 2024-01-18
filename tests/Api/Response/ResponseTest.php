<?php

namespace Findologic\Tests\Api\Response;

use PHPUnit\Framework\TestCase;
use Findologic\Api\Response\Response;
use Plenty\Plugin\Translation\Translator;
use Findologic\Api\Response\ResponseParser;
use PHPUnit\Framework\MockObject\MockObject;
use Findologic\Tests\Helpers\MockResponseHelper;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;

/**
 * Class ResponseParserTest
 * @package Findologic\Tests
 */
class ResponseTest extends TestCase
{
    use MockResponseHelper;
    
    protected \ApiResponse $response;

    public function setUp(): void
    {
        $this->response = new \ApiResponse(new Json10Response($this->getMockResponse('someResults.json')));
    }

    public function testResponseCanCreateResults(){
        
        $result = new \ApiResult($this->response->result);
        $this->assertInstanceOf(\ApiResult::class , $result);

        $resultArray = $this->response->toArray();
        
        $this->assertNotEmpty($resultArray['result']);

        $this->assertNotEmpty($resultArray['result']['metadata']);

        $this->assertNotEmpty($resultArray['result']['items']);

        $this->assertNotEmpty($resultArray['result']['variant']);

        $this->assertNotEmpty($resultArray['result']['mainFilters']);

        $this->assertNotEmpty($resultArray['result']['otherFilters']);

        print_r($resultArray);
    }

    public function testQueryInfoMessage(){
        $responseParserMock = $this->createMock(ResponseParser::class);
        // $responseParserMock->method
    }

    /**
     * @dataProvider getQueryInfoMessageProvider
     */
    public function testGetQueryInfoMessage(
        array $queryInfoMessageData,
        array $filtersData,
        string $expectedTranslation,
        array $expectedTranslationParams
    ) {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($expectedTranslation, $expectedTranslationParams)
            ->willReturn('TestTranslated');

        /** @var Response|MockObject $responseMock */
        $responseMock = $this->getResponseMock(['getData']);
        $responseMock->method('getData')
            ->withConsecutive([Response::DATA_QUERY_INFO_MESSAGE], [Response::DATA_FILTERS])
            ->willReturnOnConsecutiveCalls($queryInfoMessageData, $filtersData);

        $responseMock->getQueryInfoMessage();
    }

    public function FunctionName(Type $var = null)
    {
        # code...
    }

    public function getQueryInfoMessageProvider(): array
    {
        return [
            'No query set' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ],
                'filtersData' => [],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageDefault',
                'expectedTranslationParams' => [
                    'hits' => 0
                ]
            ],
            'No Smart Did-You-Mean data provided' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ],
                'filtersData' => [],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageQuery',
                'expectedTranslationParams' => [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Did-You-Mean query present' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => 'TestDidYouMeanQuery',
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ],
                'filtersData' => [],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageQuery',
                'expectedTranslationParams' => [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Improved query present' => [
                'queryInfoMessageData' => [
                    'originalQuery' => 'OriginalTest',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => 'improved',
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ],
                'filtersData' => [],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageQuery',
                'expectedTranslationParams' => [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Corrected query present' => [
                'queryInfoMessageData' => [
                    'originalQuery' => 'OriginalTest',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => 'corrected',
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ],
                'filtersData' => [],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageQuery',
                'expectedTranslationParams' => [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Uses shopping guide' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null,
                    'shoppingGuide' => 'testShoppingGuide'
                ],
                'filtersData' => [
                    [
                        'id' => 'TestFilter',
                        'name' => 'TestFilterDisplayName',
                        'position' => 10
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'CatDisplayName',
                        'position' => 20
                    ],
                    [
                        'id' => 'vendor',
                        'name' => 'VendorDisplayName',
                        'position' => 30
                    ]
                ],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageShoppingGuide',
                'expectedTranslationParams' => [
                    'shoppingGuide' => 'testShoppingGuide',
                    'hits' => 0
                ]
            ],
            'Category selected' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestCat',
                    'selectedVendorName' => null,
                    'shoppingGuide' => null
                ],
                'filtersData' => [
                    [
                        'id' => 'TestFilter',
                        'name' => 'TestFilterDisplayName',
                        'position' => 10
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'CatDisplayName',
                        'position' => 20
                    ],
                    [
                        'id' => 'vendor',
                        'name' => 'VendorDisplayName',
                        'position' => 30
                    ]
                ],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageCat',
                'expectedTranslationParams' => [
                    'filterName' => 'CatDisplayName',
                    'cat' => 'TestCat',
                    'hits' => 0
                ]
            ],
            'Vendor selected' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => 'TestVendor',
                    'shoppingGuide' => null
                ],
                'filtersData' => [
                    [
                        'id' => 'TestFilter',
                        'name' => 'TestFilterDisplayName',
                        'position' => 10
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'CatDisplayName',
                        'position' => 20
                    ],
                    [
                        'id' => 'vendor',
                        'name' => 'VendorDisplayName',
                        'position' => 30
                    ]
                ],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageVendor',
                'expectedTranslationParams' => [
                    'filterName' => 'VendorDisplayName',
                    'vendor' => 'TestVendor',
                    'hits' => 0
                ]
            ],
            'No search query present and category with vendor filter selected' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestChildCat',
                    'selectedVendorName' => 'TestVendor',
                    'shoppingGuide' => null
                ],
                'filtersData' => [
                    [
                        'id' => 'TestFilter',
                        'name' => 'TestFilterDisplayName',
                        'position' => 10
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'CatDisplayName',
                        'position' => 20
                    ],
                    [
                        'id' => 'vendor',
                        'name' => 'VendorDisplayName',
                        'position' => 30
                    ]
                ],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageCat',
                'expectedTranslationParams' => [
                    'filterName' => 'CatDisplayName',
                    'cat' => 'TestChildCat',
                    'hits' => 0
                ]
            ],
            'Search query present and category with vendor filter selected' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'TestQuery',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestChildCat',
                    'selectedVendorName' => 'TestVendor',
                    'shoppingGuide' => null
                ],
                'filtersData' => [
                    [
                        'id' => 'TestFilter',
                        'name' => 'TestFilterDisplayName',
                        'position' => 10
                    ],
                    [
                        'id' => 'cat',
                        'name' => 'CatDisplayName',
                        'position' => 20
                    ],
                    [
                        'id' => 'vendor',
                        'name' => 'VendorDisplayName',
                        'position' => 30
                    ]
                ],
                'expectedTranslation' => 'Findologic::Template.queryInfoMessageQuery',
                'expectedTranslationParams' => [
                    'query' => 'TestQuery',
                    'hits' => 0
                ]
            ]
        ];
    }

    public function smartDidYouMeanQueryProvider(): array
    {
        return [
            'Smart Did-You-Mean is not triggered if there is an empty query' => [
                'queryInfoMessageData' => [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                ],
                'expectedTranslationKey' => '',
                'expectedTranslationParams' => [],
            ],
            'Smart Did-You-Mean with did you mean query' => [
                'queryInfoMessageData' => [
                    'originalQuery' => 'bok',
                    'didYouMeanQuery' => 'book',
                    'currentQuery' => 'bok',
                    'queryStringType' => null,
                ],
                'expectedTranslationKey' => 'Findologic::Template.didYouMeanQuery',
                'expectedTranslationParams' => [
                    'originalQuery' => 'bok',
                    'alternativeQuery' => 'book'
                ],
            ],
            'Smart Did-You-Mean with improved query' => [
                'queryInfoMessageData' => [
                    'originalQuery' => 'bok',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'book',
                    'queryStringType' => 'improved',
                ],
                'expectedTranslationKey' => 'Findologic::Template.improvedQuery',
                'expectedTranslationParams' => [
                    'originalQuery' => 'bok',
                    'alternativeQuery' => 'book'
                ],
            ],
            'Smart Did-You-Mean with corrected query' => [
                'queryInfoMessageData' => [
                    'originalQuery' => 'bok',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'book',
                    'queryStringType' => 'corrected',
                ],
                'expectedTranslationKey' => 'Findologic::Template.correctedQuery',
                'expectedTranslationParams' => [
                    'originalQuery' => 'bok',
                    'alternativeQuery' => 'book'
                ],
            ],
        ];
    }

    /**
     * @dataProvider smartDidYouMeanQueryProvider
     */
    public function testSmartDidYouMean(
        array $queryInfoMessageData,
        string $expectedTranslationKey,
        array $expectedTranslationParams
    ) {
        if ($expectedTranslationKey && $expectedTranslationParams) {
            $this->translator->expects($this->once())
                ->method('trans')
                ->with($expectedTranslationKey, $expectedTranslationParams)
                ->willReturn('');
        } else {
            $this->translator
                ->expects($this->never())
                ->method('trans');
        }

        /** @var Response|MockObject $responseMock */
        $responseMock = $this->getResponseMock(['getData']);
        $responseMock->method('getData')
            ->withConsecutive([Response::DATA_QUERY_INFO_MESSAGE])
            ->willReturnOnConsecutiveCalls($queryInfoMessageData);

        $responseMock->getSmartDidYouMean();
    }

    /**
     * @param array|null $methods
     * @return Response|MockObject
     */
    protected function getResponseMock($methods = null)
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->setConstructorArgs([
                'translator' => $this->translator
            ])
            ->setMethods($methods);

        return $responseMock->getMock();
    }
}
