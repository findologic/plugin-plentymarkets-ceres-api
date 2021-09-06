<?php

namespace Findologic\Tests\Services\Search;

use Ceres\Helper\ExternalSearchOptions;
use Findologic\Services\Search\ParametersHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Plenty\Plugin\Http\Request;
use stdClass;

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
                'isSearch' => false,
                'hasFilters' => false,
                'ceresConfigData' => [
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
                'expectedResult' => [
                    'default.recommended_sorting' => 'Ceres::Template.itemRecommendedSorting',
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.availability.averageDays_asc' => 'Ceres::Template.itemAvailabilityAverageDays_asc',
                    'variation.availability.averageDays_desc' => 'Ceres::Template.itemAvailabilityAverageDays_desc',
                    'variation.number_asc' => 'Ceres::Template.itemVariationCustomNumber_asc',
                    'variation.number_desc' => 'Ceres::Template.itemVariationCustomNumber_desc',
                    'variation.updatedAt_asc' => 'Ceres::Template.itemVariationLastUpdateTimestamp_asc',
                    'variation.updatedAt_desc' => 'Ceres::Template.itemVariationLastUpdateTimestamp_desc',
                    'item.manufacturer.externalName_asc' => 'Ceres::Template.itemProducerName_asc',
                    'item.manufacturer.externalName_desc' => 'Ceres::Template.itemProducerName_desc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                    'item.score' => 'Ceres::Template.itemRelevance',
                    'item.random' => 'Ceres::Template.itemRandom'
                ]
            ],
            'Sorting options for category page with filter selected' => [
                'isSearch' => false,
                'hasFilters' => true,
                'ceresConfigData' => [
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
                'expectedResult' => [
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
                'isSearch' => true,
                'hasFilters' => false,
                'ceresConfigData' => [
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
                'expectedResult' => [
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
                'isSearch' => true,
                'hasFilters' => false,
                'ceresConfigData' => [
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
                'expectedResult' => [
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
     */
    public function testGetSortingOptions(
        bool $isSearch,
        bool $isFiltersSet,
        array $ceresConfigData,
        array $expectedResult
    ) {
        $config = $this->createCeresConfigObject($ceresConfigData);

        /** @var ParametersHandler|MockObject $parametersHandlerMock */
        $parametersHandlerMock = $this->getMockBuilder(ParametersHandler::class)
            ->setMethods(['getConfig'])
            ->getMock();
        $parametersHandlerMock->expects($this->once())->method('getConfig')->willReturn($config);

        $this->assertEquals($parametersHandlerMock->getSortingOptions($isSearch, $isFiltersSet), $expectedResult);
    }

    public function handlePaginationAndSortingProvider()
    {
        return [
            'Sorting options for search page with supported default' => [
                'isSearch' => true,
                'hasFilters' => false,
                'defaultSorting' => 'texts.name1_asc',
                'configData' => [
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
                'sortingOptions' => [
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
                'defaultOption' => 'texts.name1_asc'
            ],
            'Sorting options for category page with supported default' => [
                'isSearch' => false,
                'hasFilters' => false,
                'defaultSorting' => 'texts.name1_asc',
                'configData' => [
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
                'sortingOptions' => [
                    'default.recommended_sorting' => 'Ceres::Template.itemRecommendedSorting',
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.availability.averageDays_asc' => 'Ceres::Template.itemAvailabilityAverageDays_asc',
                    'variation.availability.averageDays_desc' => 'Ceres::Template.itemAvailabilityAverageDays_desc',
                    'variation.number_asc' => 'Ceres::Template.itemVariationCustomNumber_asc',
                    'variation.number_desc' => 'Ceres::Template.itemVariationCustomNumber_desc',
                    'variation.updatedAt_asc' => 'Ceres::Template.itemVariationLastUpdateTimestamp_asc',
                    'variation.updatedAt_desc' => 'Ceres::Template.itemVariationLastUpdateTimestamp_desc',
                    'item.manufacturer.externalName_asc' => 'Ceres::Template.itemProducerName_asc',
                    'item.manufacturer.externalName_desc' => 'Ceres::Template.itemProducerName_desc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                    'item.random' => 'Ceres::Template.itemRandom'
                ],
                'defaultOption' => 'texts.name1_asc'
            ],
            'Sorting options for category page with unsupported default but no filters uses configured default' => [
                'isSearch' => false,
                'hasFilters' => false,
                'defaultSorting' => 'item.random',
                'configData' => [
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
                'sortingOptions' => [
                    'default.recommended_sorting' => 'Ceres::Template.itemRecommendedSorting',
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.availability.averageDays_asc' => 'Ceres::Template.itemAvailabilityAverageDays_asc',
                    'variation.availability.averageDays_desc' => 'Ceres::Template.itemAvailabilityAverageDays_desc',
                    'variation.number_asc' => 'Ceres::Template.itemVariationCustomNumber_asc',
                    'variation.number_desc' => 'Ceres::Template.itemVariationCustomNumber_desc',
                    'variation.updatedAt_asc' => 'Ceres::Template.itemVariationLastUpdateTimestamp_asc',
                    'variation.updatedAt_desc' => 'Ceres::Template.itemVariationLastUpdateTimestamp_desc',
                    'item.manufacturer.externalName_asc' => 'Ceres::Template.itemProducerName_asc',
                    'item.manufacturer.externalName_desc' => 'Ceres::Template.itemProducerName_desc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                    'item.random' => 'Ceres::Template.itemRandom'
                ],
                'defaultOption' => 'item.random'
            ],
            'Sorting options for category page with unsupported default but with filter uses default fallback' => [
                'isSearch' => false,
                'hasFilters' => true,
                'defaultSorting' => 'item.random',
                'configData' => [
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
                'sortingOptions' => [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                ],
                'defaultOption' => 'item.score'
            ],
            'Sorting options for category page with a filter selected' => [
                'isSearch' => false,
                'hasFilters' => true,
                'defaultSorting' => 'texts.name1_asc',
                'configData' => [
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
                'sortingOptions' => [
                    'texts.name1_asc' => 'Ceres::Template.itemName_asc',
                    'texts.name1_desc' => 'Ceres::Template.itemName_desc',
                    'sorting.price.avg_asc' => 'Ceres::Template.itemPrice_asc',
                    'sorting.price.avg_desc' => 'Ceres::Template.itemPrice_desc',
                    'variation.createdAt_desc' => 'Ceres::Template.itemVariationCreateTimestamp_desc',
                    'variation.createdAt_asc' => 'Ceres::Template.itemVariationCreateTimestamp_asc',
                    'variation.position_asc' => 'Ceres::Template.itemVariationTopseller_asc',
                    'variation.position_desc' => 'Ceres::Template.itemVariationTopseller_desc',
                ],
                'defaultOption' => 'texts.name1_asc'
            ],
        ];
    }

    /**
     * @dataProvider handlePaginationAndSortingProvider
     */
    public function testHandlePaginationAndSorting(
        bool $isSearch,
        bool $isFiltersSet,
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

        $returnedUri = 'test.com';

        if ($isSearch) {
            $returnedUri .= '/search';
        }

        if ($isFiltersSet) {
            $requestMock->expects($this->once())->method('all')->willReturn([
                'attrib' => [
                    'vendor' => [
                        'Test_Manufacturer'
                    ]
                ]
            ]);
        }

        $requestMock->expects($this->any())->method('getUri')->willReturn($returnedUri);

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

        $this->assertEquals($sortingOptions, $externalSearchOptions->getSortingOptions());
        $this->assertEquals($defaultOption, $externalSearchOptions->getDefaultSortingOption());
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
        $sorting = new stdClass();
        $sorting->data = $sortingData;
        $sorting->defaultSortingSearch = $defaultSortingSearch;
        $sorting->defaultSorting = $defaultSorting;

        $config = new stdClass();
        $config->sorting = $sorting;

        return $config;
    }
}
