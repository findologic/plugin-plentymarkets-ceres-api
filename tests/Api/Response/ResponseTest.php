<?php

namespace Findologic\Tests\Api\Response;

use Findologic\Api\Response\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Plenty\Plugin\Translation\Translator;

/**
 * Class ResponseParserTest
 * @package Findologic\Tests
 */
class ResponseTest extends TestCase
{
    /**
     * @var Translator|MockObject
     */
    protected $translator;

    public function setUp()
    {
        $this->translator = $this->getMockBuilder(Translator::class)->disableOriginalConstructor()->setMethods(['trans'])->getMock();
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

    public function getQueryInfoMessageProvider(): array
    {
        return [
            'No query set' => [
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null
                ],
                [],
                'Findologic::Template.queryInfoMessageDefault',
                [
                    'hits' => 0
                ]
            ],
            'No Smart Did-You-Mean data provided' => [
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null
                ],
                [],
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Did-You-Mean query present' => [
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => 'TestDidYouMeanQuery',
                    'currentQuery' => 'Test',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null
                ],
                [],
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Improved query present' => [
                [
                    'originalQuery' => 'OriginalTest',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => 'improved',
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null
                ],
                [],
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Corrected query present' => [
                [
                    'originalQuery' => 'OriginalTest',
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'Test',
                    'queryStringType' => 'corrected',
                    'selectedCategoryName' => null,
                    'selectedVendorName' => null
                ],
                [],
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => 'Test',
                    'hits' => 0
                ]
            ],
            'Category selected' => [
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestCat',
                    'selectedVendorName' => null
                ],
                [
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
                'Findologic::Template.queryInfoMessageCat',
                [
                    'filterName' => 'CatDisplayName',
                    'cat' => 'TestCat',
                    'hits' => 0
                ]
            ],
            'Vendor selected' => [
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => null,
                    'selectedVendorName' => 'TestVendor'
                ],
                [
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
                'Findologic::Template.queryInfoMessageVendor',
                [
                    'filterName' => 'VendorDisplayName',
                    'vendor' => 'TestVendor',
                    'hits' => 0
                ]
            ],
            'No search query present and category with vendor filter selected' => [
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => '',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestChildCat',
                    'selectedVendorName' => 'TestVendor'
                ],
                [
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
                'Findologic::Template.queryInfoMessageCat',
                [
                    'filterName' => 'CatDisplayName',
                    'cat' => 'TestChildCat',
                    'hits' => 0
                ]
            ],
            'Search query present and category with vendor filter selected' => [
                [
                    'originalQuery' => null,
                    'didYouMeanQuery' => null,
                    'currentQuery' => 'TestQuery',
                    'queryStringType' => null,
                    'selectedCategoryName' => 'TestChildCat',
                    'selectedVendorName' => 'TestVendor'
                ],
                [
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
                'Findologic::Template.queryInfoMessageQuery',
                [
                    'query' => 'TestQuery',
                    'hits' => 0
                ]
            ]
        ];
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
