<?php

namespace Findologic\Tests\Api\Request;

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

    public function providerSetSearchParams()
    {
        return [
            'Category page request' => [
                [
                    'query' => 'Test',
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'color' => ['red', 'blue'],
                        'cat' => ['Category 2'],
                    ],
                    Plugin::API_PARAMETER_SORT_ORDER => 'price ASC',
                    Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE => 20,
                    Plugin::API_PARAMETER_PAGINATION_START => 10,
                ],
                [
                    'parentCategoryId' => null,
                    'name' => 'Category'
                ],
                [
                    'query' => 'Test',
                    'properties' => [
                        0 => 'main_variation_id'
                    ],
                    'attrib' => [
                        'color' => ['red', 'blue']
                    ],
                    'selected' => ['cat' => ['Category']],
                    'order' => 'price ASC',
                    'count' => 20,
                    'first' => 10
                ]
            ],
            'Search page request' => [
                [
                    'query' => 'Test',
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    Plugin::API_PARAMETER_SORT_ORDER => 'price DESC',
                    Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE => 10,
                    Plugin::API_PARAMETER_PAGINATION_START => 0,
                ],
                false,
                [
                    'query' => 'Test',
                    'properties' => [
                        0 => 'main_variation_id'
                    ],
                    'attrib' => [
                        'size' => ['l', 'xl'],
                        'cat' => 'Category'
                    ],
                    'order' => 'price DESC',
                    'count' => 10
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerSetSearchParams
     */
    public function testSetSearchParams($parameters, $category, $expectedParameters)
    {
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $httpRequestMock->expects($this->once())->method('all')->willReturn($parameters);

        $categoryMock = null;
        $parametersBuilderMock = $this->getParametersBuilderMock();

        if ($category) {
            $categoryMock = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
            $categoryMock->parentCategoryId = $category['parentCategoryId'];

            $details = new \stdClass();
            $details->name = $category['name'];

            $categoryMock->details = [$details];
        }

        $result = $parametersBuilderMock->setSearchParams($requestMock, $httpRequestMock, $categoryMock);

        $this->assertEquals($expectedParameters, $result->getParams());
    }

    public function providerGetCategoryName()
    {
        return [
            [
                [
                    2 => ['parentCategoryId' => 1, 'name' => 'Test1'],
                    1 => ['parentCategoryId' => 0, 'name' => 'Test0']
                ],
                'Test1_Test0'
            ]
        ];
    }

    /**
     * @dataProvider providerGetCategoryName
     */
    public function testGetCategoryName($categories, $expectedCategoryName)
    {
        $mockedCategories = [];

        foreach ($categories as $id => $category) {
            $categoryMock = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
            $categoryMock->parentCategoryId = $category['parentCategoryId'];

            $details = new \stdClass();
            $details->name = $category['name'];
            $categoryMock->details = [$details];

            $mockedCategories[] =[$id, $categoryMock];
        }

        $this->categoryService->expects($this->any())->method('get')->willReturnMap($mockedCategories);

        $parametersBuilderMock = $this->getParametersBuilderMock();

        $this->assertEquals($expectedCategoryName, $parametersBuilderMock->getCategoryName($mockedCategories[0][1]));
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