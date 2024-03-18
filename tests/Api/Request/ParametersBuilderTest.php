<?php

namespace Findologic\Tests\Api\Request;

use Ceres\Helper\ExternalSearch;
use Findologic\Api\Request\ParametersBuilder;
use Findologic\Api\Request\Request;
use Findologic\Constants\Plugin;
use Findologic\Helpers\Tags;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;
use IO\Services\CategoryService;
use Plenty\Modules\Category\Models\Category;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ParametersBuilderTest
 * @package Findologic\Tests\Api\Request
 */
class ParametersBuilderTest extends TestCase
{
    /**
     * @var CategoryService|MockObject
     */
    protected $categoryService;

    /**
     * @var LoggerFactory|MockObject
     */
    protected $loggerFactory;

    /**
     * @var LoggerContract|MockObject
     */
    protected $logger;

    /**
     * @var Tags|MockObject
     */
    protected $tagsHelper;

    public function setUp(): void
    {
        $this->categoryService = $this->getMockBuilder(CategoryService::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
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
        $this->tagsHelper = $this->getMockBuilder(Tags::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();
    }

    public function setSearchParamsProvider()
    {
        return [
            'Category page request' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'color' => ['red', 'blue'],
                        'cat' => ['Category 2'],
                    ],
                ],
                'requestUri' => 'https://www.test.com/testCategory',
                'category' => [
                    'parentCategoryId' => null,
                    'name' => 'Category'
                ],
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'color' => ['red', 'blue'],
                            'cat' => ['Category 2']
                        ]
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => 2
                    ],
                    'isTagPage' => null,
                    'category' => 1,
                    'categoryName' => 'Category'
                ]
            ],
            'Category page request with subcategory selected' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'color' => ['red', 'blue'],
                        'cat' => ['Category 2', 'Category 1'],
                    ],
                ],
                'requestUri' => 'https://www.test.com/testCategory',
                'category' => [
                    'parentCategoryId' => null,
                    'name' => 'Category'
                ],
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'color' => ['red', 'blue'],
                            'cat' => ['Category 2', 'Category 1']
                        ]
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => 2
                    ],
                    'isTagPage' => null,
                    'category' => 1,
                    'categoryName' => 'Category',
                ]
            ],
            'Category page request with same price slider min and max values' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'price' => [
                            'min' => 77,
                            'max' => 77
                        ]
                    ],
                ],
                'requestUri' => 'https://www.test.com/testCategory',
                'category' => [
                    'parentCategoryId' => null,
                    'name' => 'Category'
                ],
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'price' => [
                                'min' => 77,
                                'max' => 77
                            ]
                        ]
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => 2
                    ],
                    'isTagPage' => null,
                    'category' => 1,
                    'categoryName' => 'Category',
                ]
            ],
            'Search page request' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                ],
                'requestUri' => 'https://www.test.com/search?query=Test',
                'category' => false,
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'size' => ['l', 'xl'],
                            'cat' => 'Category'
                        ]
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => null
                    ],
                    'isTagPage' => null,
                    'category' => null,
                    'categoryName' => null,
                ]
            ],
            'Search page request with same price slider min and max values' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'price' => [
                            'min' => 77,
                            'max' => 77
                        ]
                    ],
                ],
                'requestUri' => 'https://www.test.com/search?query=Test',
                'category' => false,
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'price' => [
                                'min' => 77,
                                'max' => 77
                            ]
                        ]
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => null
                    ],
                    'isTagPage' => null,
                    'category' => null,
                    'categoryName' => null,
                ]
            ],
            'Force original query enabled' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => 1
                ],
                'requestUri' => 'https://www.test.com/search?query=Test',
                'category' => false,
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'size' => ['l', 'xl'],
                            'cat' => 'Category'
                        ],
                        Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => true
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => null
                    ],
                    'isTagPage' => null,
                    'category' => null,
                    'categoryName' => null,
                ]
            ],
            'Force original query disabled' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => 0
                ],
                'requestUri' => 'https://www.test.com/search?query=Test',
                'category' => false,
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'size' => ['l', 'xl'],
                            'cat' => 'Category'
                        ],
                        Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => 0
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => null
                    ],
                    'isTagPage' => false,
                    'category' => false,
                    'categoryName' => '',
                ]
            ],
            'Tag page request' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => 0
                ],
                'requestUri' => 'https://www.test.com/aaaaa_t125',
                'category' => false,
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'size' => ['l', 'xl'],
                            'cat' => 'Category'
                        ],
                        Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => 0
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => null
                    ],
                    'isTagPage' => 1,
                    'tagId' => 125,
                    'category' => false,
                    'categoryName' => '',
                ]
            ],
            'Request with same attributes' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'vendor' => [
                            'A & C Design',
                            'A & C Design',
                        ]
                    ],
                ],
                'requestUri' => 'https://www.test.com/search?query=Test',
                'category' => false,
                'expectedParameters' => [
                    'parameters' => [
                    'attrib' => [
                        'vendor' => [
                            'A & C Design',
                            'A & C Design',
                        ]
                    ],
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => null
                    ],
                    'isTagPage' => false,
                    'category' => false,
                    'categoryName' => '',
                ]
            ],
            'Request with same attributes and range slider filter with same min and max' => [
                'parameters' => [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'vendor' => [
                            'A & C Design',
                            'A & C Design',
                        ],
                        'price' => [
                            'min' => 77,
                            'max' => 77
                        ]
                    ],
                ],
                'requestUri' => 'https://www.test.com/search?query=Test',
                'category' => false,
                'expectedParameters' => [
                    'parameters' => [
                        'attrib' => [
                            'vendor' => [
                                'A & C Design',
                                'A & C Design',
                            ],
                            'price' => [
                                'min' => 77,
                                'max' => 77
                            ],
                        ],
                    ],
                    'externalSearch' => [
                        'searchString' => 'Test',
                        'sorting' => 'sorting.price.avg_asc',
                        'itemsPerPage' => 10,
                        'page' => 1,
                        'categoryId' => null
                    ],
                    'isTagPage' => false,
                    'category' => false,
                    'categoryName' => '',
                ]
            ],
        ];
    }

    /**
     * @dataProvider setSearchParamsProvider
     *
     * @param array $parameters
     * @param string $requestUri
     * @param array|bool $category
     * @param array $expectedParameters
     */
    public function testSetSearchParams(array $parameters, string $requestUri, $category, array $expectedParameters)
    {
        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $httpRequestMock->expects($this->once())->method('all')->willReturn($parameters);
        $httpRequestMock->expects($this->any())->method('getUri')->willReturn($requestUri);

        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $searchQueryMock->searchString = 'Test';
        $searchQueryMock->sorting = 'sorting.price.avg_asc';
        $searchQueryMock->itemsPerPage = 10;
        $searchQueryMock->page = 1;
        $searchQueryMock->categoryId = null;

        $categoryMock = null;
        $parametersBuilderMock = $this->getParametersBuilderMock();

        if ($category) {
            $categoryMock = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
            $categoryMock->parentCategoryId = $category['parentCategoryId'];

            $details = new \stdClass();
            $details->name = $category['name'];

            $categoryMock->details = [$details];

            $searchQueryMock->categoryId = 2;
        }

        $result = $parametersBuilderMock->setSearchParams(
            $httpRequestMock,
            $searchQueryMock,
            $categoryMock
        );

        $this->assertEquals($expectedParameters, $result);
    }

    public function testGetCategoryName()
    {
        $parentCategoryMock = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
        $parentCategoryMock->parentCategoryId = 0;
        $details = new \stdClass();
        $details->name = 'Test0';
        $parentCategoryMock->details = [$details];

        $categoryMock = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
        $categoryMock->parentCategoryId = 1;
        $details = new \stdClass();
        $details->name = 'Test1';
        $categoryMock->details = [$details];

        $this->categoryService->expects($this->once())->method('get')->willReturn($parentCategoryMock);

        $parametersBuilderMock = $this->getParametersBuilderMock();

        $this->assertEquals('Test0_Test1', $parametersBuilderMock->getCategoryName($categoryMock));
    }

    /**
     * @param array|null $methods
     * @return ParametersBuilder|MockObject
     */
    protected function getParametersBuilderMock($methods = null)
    {
        if (!is_array($methods)) {
            $methods = [];
        }

        $methods = array_merge($methods, ['getCategoryService']);

        $parametersBuilderMock = $this->getMockBuilder(ParametersBuilder::class)
            ->setConstructorArgs([
                'loggerFactory' => $this->loggerFactory,
                'tagsHelper' => $this->tagsHelper
            ])
            ->setMethods($methods)
            ->getMock();

        $parametersBuilderMock->expects($this->any())->method('getCategoryService')->willReturn($this->categoryService);

        return $parametersBuilderMock;
    }
}
