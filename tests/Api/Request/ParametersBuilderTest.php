<?php

namespace Findologic\Tests\Api\Request;

use Ceres\Helper\ExternalSearch;
use Findologic\Api\Request\ParametersBuilder;
use Findologic\Api\Request\Request;
use Findologic\Constants\Plugin;
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

    public function setUp()
    {
        $this->categoryService = $this->getMockBuilder(CategoryService::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
    }

    public function setSearchParamsProvider()
    {
        return [
            'Category page request' => [
                [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'color' => ['red', 'blue'],
                        'cat' => ['Category 2'],
                    ],
                ],
                [
                    'parentCategoryId' => null,
                    'name' => 'Category'
                ],
                [
                    'query' => 'Test',
                    'properties' => [
                        0 => 'variation_id'
                    ],
                    'attrib' => [
                        'color' => ['red', 'blue']
                    ],
                    'selected' => ['cat' => ['Category']],
                    'order' => 'price ASC',
                    'count' => 0,
                    'first' => 0
                ]
            ],
            'Search page request' => [
                [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                ],
                false,
                [
                    'query' => 'Test',
                    'properties' => [
                        0 => 'variation_id'
                    ],
                    'attrib' => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    'order' => 'price ASC',
                    'count' => 10
                ]
            ],
            'Force original query enabled' => [
                [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => 1
                ],
                false,
                [
                    'query' => 'Test',
                    'properties' => [
                        0 => 'variation_id'
                    ],
                    'attrib' => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    'order' => 'price ASC',
                    'count' => 10,
                    Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => true
                ]
            ],
            'Force original query disabled' => [
                [
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY => 0
                ],
                false,
                [
                    'query' => 'Test',
                    'properties' => [
                        0 => 'variation_id'
                    ],
                    'attrib' => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    'order' => 'price ASC',
                    'count' => 10
                ]
            ]
        ];
    }

    /**
     * @dataProvider setSearchParamsProvider
     *
     * @param array $parameters
     * @param array|bool $category
     * @param array $expectedParameters
     */
    public function testSetSearchParams(array $parameters, $category, array $expectedParameters)
    {
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $httpRequestMock->expects($this->once())->method('all')->willReturn($parameters);

        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods([])->getMock();
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

        $result = $parametersBuilderMock->setSearchParams($requestMock, $httpRequestMock, $searchQueryMock, $categoryMock);

        $this->assertEquals($expectedParameters, $result->getParams());
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
                'loggerFactory' => $this->loggerFactory
            ])
            ->setMethods($methods)
            ->getMock();

        $parametersBuilderMock->expects($this->any())->method('getCategoryService')->willReturn($this->categoryService);

        return $parametersBuilderMock;
    }
}
