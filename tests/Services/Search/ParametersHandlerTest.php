<?php

namespace Findologic\Tests\Services\Search;

use Ceres\Helper\ExternalSearchOptions;
use Findologic\Services\Search\ParametersHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Plenty\Plugin\Http\Request;

/**
 * Class SearchServiceTest
 * @package Findologic\Tests
 */
class ParametersHandlerTest extends TestCase
{
    public function getSortingOptionsProvider()
    {
        return [
            'Sorting options for category page' => [
                false,
                [
                    'default.recommended_sorting',
                    'texts.name1_asc',
                    'texts.name1_desc',
                    'sorting.price.avg_asc',
                    'sorting.price.avg_desc',
                    'variation.createdAt_desc',
                    'variation.createdAt_asc',
                    'variation.availability.averageDays_asc',
                    'variation.availability.averageDays_desc',
                    'variation.number_asc',
                    'variation.number_desc',
                    'variation.updatedAt_asc',
                    'variation.updatedAt_desc',
                    'item.manufacturer.externalName_asc',
                    'item.manufacturer.externalName_desc',
                    'variation.position_asc',
                    'variation.position_desc',
                    'item.score',
                    'item.random'
                ],
                [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                    'item.score' => 'Ceres::Template.itemRelevance'
                ]
            ],
            'Sorting options for search page' => [
                true,
                [
                    'default.recommended_sorting',
                    'texts.name1_asc',
                    'texts.name1_desc',
                    'sorting.price.avg_asc',
                    'sorting.price.avg_desc',
                    'variation.createdAt_desc',
                    'variation.createdAt_asc',
                    'variation.availability.averageDays_asc',
                    'variation.availability.averageDays_desc',
                    'variation.number_asc',
                    'variation.number_desc',
                    'variation.updatedAt_asc',
                    'variation.updatedAt_desc',
                    'item.manufacturer.externalName_asc',
                    'item.manufacturer.externalName_desc',
                    'variation.position_asc',
                    'variation.position_desc',
                    'item.score',
                    'item.random'
                ],
                [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                    'item.score' => 'Ceres::Template.itemRelevance'
                ]
            ],
            'Force option "Relevance" for search page' => [
                true,
                [
                    'default.recommended_sorting',
                    'texts.name1_asc',
                    'texts.name1_desc',
                    'sorting.price.avg_asc',
                    'sorting.price.avg_desc',
                    'variation.createdAt_desc',
                    'variation.createdAt_asc',
                    'variation.availability.averageDays_asc',
                    'variation.availability.averageDays_desc',
                    'variation.number_asc',
                    'variation.number_desc',
                    'variation.updatedAt_asc',
                    'variation.updatedAt_desc',
                    'item.manufacturer.externalName_asc',
                    'item.manufacturer.externalName_desc',
                    'variation.position_asc',
                    'variation.position_desc',
                    'item.random'
                ],
                [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                    'item.score' => 'Ceres::Template.itemRelevance'
                ]
            ]
        ];
    }

    /**
     * @dataProvider getSortingOptionsProvider
     *
     * @param bool $isSearch
     * @param array $ceresConfigData
     * @param array $expectedResult
     */
    public function testGetSortingOptions(bool $isSearch, array $ceresConfigData, array $expectedResult)
    {
        $config = $this->createCeresConfigObject($ceresConfigData);

        /** @var ParametersHandler|MockObject $parametersHandlerMock */
        $parametersHandlerMock = $this->getMockBuilder(ParametersHandler::class)
            ->setMethods(['getConfig'])
            ->getMock();
        $parametersHandlerMock->expects($this->once())->method('getConfig')->willReturn($config);

        $this->assertEquals($parametersHandlerMock->getSortingOptions($isSearch), $expectedResult);
    }

    public function handlePaginationAndSortingProvider()
    {
        return [
            'Sorting options for search page with supported default' => [
                true,
                'texts.name1_asc',
                [
                    'default.recommended_sorting',
                    'texts.name1_asc',
                    'texts.name1_desc',
                    'sorting.price.avg_asc',
                    'sorting.price.avg_desc',
                    'variation.createdAt_desc',
                    'variation.createdAt_asc',
                    'variation.availability.averageDays_asc',
                    'variation.availability.averageDays_desc',
                    'variation.number_asc',
                    'variation.number_desc',
                    'variation.updatedAt_asc',
                    'variation.updatedAt_desc',
                    'item.manufacturer.externalName_asc',
                    'item.manufacturer.externalName_desc',
                    'variation.position_asc',
                    'variation.position_desc',
                    'item.random'
                ],
                [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                    'item.score' => 'Ceres::Template.itemRelevance'
                ],
                'texts.name1_asc'
            ],
            'Sorting options for category page with supported default' => [
                false,
                'texts.name1_asc',
                [
                    'default.recommended_sorting',
                    'texts.name1_asc',
                    'texts.name1_desc',
                    'sorting.price.avg_asc',
                    'sorting.price.avg_desc',
                    'variation.createdAt_desc',
                    'variation.createdAt_asc',
                    'variation.availability.averageDays_asc',
                    'variation.availability.averageDays_desc',
                    'variation.number_asc',
                    'variation.number_desc',
                    'variation.updatedAt_asc',
                    'variation.updatedAt_desc',
                    'item.manufacturer.externalName_asc',
                    'item.manufacturer.externalName_desc',
                    'variation.position_asc',
                    'variation.position_desc',
                    'item.random'
                ],
                [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc'
                ],
                'texts.name1_asc'
            ],
            'Sorting options for category page with unsupported default' => [
                false,
                'item.random',
                [
                    'default.recommended_sorting',
                    'texts.name1_asc',
                    'texts.name1_desc',
                    'sorting.price.avg_asc',
                    'sorting.price.avg_desc',
                    'variation.createdAt_desc',
                    'variation.createdAt_asc',
                    'variation.availability.averageDays_asc',
                    'variation.availability.averageDays_desc',
                    'variation.number_asc',
                    'variation.number_desc',
                    'variation.updatedAt_asc',
                    'variation.updatedAt_desc',
                    'item.manufacturer.externalName_asc',
                    'item.manufacturer.externalName_desc',
                    'variation.position_asc',
                    'variation.position_desc',
                    'item.random'
                ],
                [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc'
                ],
                'item.score'
            ]
        ];
    }

    /**
     * @dataProvider handlePaginationAndSortingProvider
     *
     * @param bool $isSearch
     * @param string $defaultSorting
     * @param array $configData
     * @param array $sortingOptions
     * @param string $defaultOption
     */
    public function testHandlePaginationAndSorting(
        bool $isSearch,
        string $defaultSorting,
        array $configData,
        array $sortingOptions,
        string $defaultOption
    ) {
        /** @var ExternalSearchOptions $externalSearchOptions */
        $externalSearchOptions = new ExternalSearchOptions();

        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)
            ->setMethods([])
            ->getMock();

        if ($isSearch) {
            $requestMock->expects($this->once())->method('getUri')->willReturn('test.com/search');
        }

        $config = $this->createCeresConfigObject(
            $configData,
            $defaultSorting,
            $isSearch ? '' : $defaultSorting
        );

        /** @var ParametersHandler|MockObject $parametersHandlerMock */
        $parametersHandlerMock = $this->getMockBuilder(ParametersHandler::class)
            ->setMethods(['getConfig', 'getItemsPerPage'])
            ->getMock();
        $parametersHandlerMock->expects($this->any())->method('getConfig')->willReturn($config);

        $parametersHandlerMock->handlePaginationAndSorting($externalSearchOptions, $requestMock);

        $this->assertEquals($externalSearchOptions->getSortingOptions(), $sortingOptions);
        $this->assertEquals($externalSearchOptions->getDefaultSortingOption(), $defaultOption);
    }

    /**
     * @param array $sortingData
     * @param string $defaultSortingSearch
     * @param string $defaultSorting
     * @return object
     */
    protected function createCeresConfigObject(
        array $sortingData = [],
        $defaultSortingSearch = '',
        $defaultSorting = ''
    ) {
        return $config = (object) array(
            'sorting' => (object) array(
                'data' => $sortingData,
                'defaultSortingSearch' => $defaultSortingSearch,
                'defaultSorting' => $defaultSorting
            )
        );
    }
}
