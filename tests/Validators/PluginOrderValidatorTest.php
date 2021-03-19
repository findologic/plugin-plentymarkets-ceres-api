<?php

namespace Findologic\Tests\Validators;

use Findologic\Validators\PluginOrderValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Modules\Plugin\PluginSet\Models\PluginSetEntry;

class PluginOrderValidatorTest extends TestCase
{
    /**
     * @var PluginOrderValidator|MockObject
     */
    private $validatorMock;

    /**
     * @var PluginSetRepositoryContract|MockObject
     */
    private $pluginSetRepositoryMock;

    /**
     * @var PluginRepositoryContract|MockObject
     */
    private $pluginRepositoryMock;

    public function setup()
    {
        /** @var PluginSetRepositoryContract|MockObject $pluginSetRepositoryMock */
        $this->pluginSetRepositoryMock = $this->getMockBuilder(PluginSetRepositoryContract::class)
            ->setMethods(['getCurrentPluginSetId'])
            ->getMockForAbstractClass();

        /** @var PluginRepositoryContract|MockObject $pluginRepositoryMock */
        $this->pluginRepositoryMock = $this->getMockBuilder(PluginRepositoryContract::class)
            ->setMethods(['getCurrentPluginSetId'])
            ->getMockForAbstractClass();

        /** @var PluginOrderValidator|MockObject $validatorMock */
        $this->validatorMock = $this->getMockBuilder(PluginOrderValidator::class)
            ->setMethods(['getPluginRepository', 'getPluginSetRepository'])
            ->getMock();
        $this->validatorMock->method('getPluginSetRepository')->willReturn($this->pluginSetRepositoryMock);
        $this->validatorMock->method('getPluginRepository')->willReturn($this->pluginRepositoryMock);
    }

    /**
     * @dataProvider validationTestDataProvider
     * @param array $ioPluginSetEntriesData
     * @param array $findologicPluginSetEntriesData
     * @param bool $expectedResult
     */
    public function testValidation($ioPluginSetEntriesData, $findologicPluginSetEntriesData, $expectedResult)
    {
        $this->pluginSetRepositoryMock->method('getCurrentPluginSetId')->willReturn('1');

        $ioPluginMock = $this->getMockForAbstractClass(Plugin::class);

        $ioPluginSetEntries = [];
        foreach ($ioPluginSetEntriesData as $ioPluginSetEntryData) {
            $ioPluginSetEntries[] = $this->buildPluginSetEntryMock($ioPluginSetEntryData);
        }
        $ioPluginMock->pluginSetEntries = $ioPluginSetEntries;

        $findologicPluginMock = $this->getMockForAbstractClass(Plugin::class);

        $findologicPluginSetEntries = [];
        foreach ($findologicPluginSetEntriesData as $findologicPluginSetEntryData) {
            $findologicPluginSetEntries[] = $this->buildPluginSetEntryMock($findologicPluginSetEntryData);
        }
        $findologicPluginMock->pluginSetEntries = $findologicPluginSetEntries;

        $this->pluginRepositoryMock->method('getPluginByName')
            ->withConsecutive(['io'], ['findologic'])
            ->willReturnOnConsecutiveCalls($ioPluginMock, $findologicPluginMock);

        $this->assertEquals($expectedResult, $this->validatorMock->validate());
    }

    public function validationTestDataProvider(): array
    {
        return [
            'It is valid when IO is loaded before Findologic in the current plugin set' => [
                [
                    [
                        'id' => 1,
                        'pluginId' => "15",
                        'pluginSetId' => "10",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "2",
                        'commit' => null,
                    ],
                    [
                        'id' => 2,
                        'pluginId' => "15",
                        'pluginSetId' => "1",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "0",
                        'commit' => null,
                    ],
                    [
                        'id' => 3,
                        'pluginId' => "15",
                        'pluginSetId' => "2",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ],
                ],
                [
                    [
                        'id' => 1,
                        'pluginId' => "16",
                        'pluginSetId' => "10",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "2",
                        'commit' => null,
                    ],
                    [
                        'id' => 2,
                        'pluginId' => "18",
                        'pluginSetId' => "1",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ]
                ],
                true
            ],
            'It is not valid when IO is loaded after Findologic in the current plugin set' => [
                [
                    [
                        'id' => 2,
                        'pluginId' => "15",
                        'pluginSetId' => "1",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ],
                ],
                [
                    [
                        'id' => 2,
                        'pluginId' => "18",
                        'pluginSetId' => "1",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ]
                ],
                false
            ],
            'It is not valid when IO is not in the current plugin set' => [
                [
                    [
                        'id' => 2,
                        'pluginId' => "15",
                        'pluginSetId' => "10",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ],
                ],
                [
                    [
                        'id' => 2,
                        'pluginId' => "18",
                        'pluginSetId' => "1",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ]
                ],
                false
            ],
            'It is not valid when Findologic is not in the current plugin set' => [
                [
                    [
                        'id' => 2,
                        'pluginId' => "15",
                        'pluginSetId' => "1",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ],
                ],
                [
                    [
                        'id' => 2,
                        'pluginId' => "18",
                        'pluginSetId' => "10",
                        'createdAt' => "2021-03-10T17:52:07+01:00",
                        'updatedAt' => "2021-03-15T12:27:35+01:00",
                        'deleted_at' => null,
                        'branchName' => null,
                        'position' => "1",
                        'commit' => null,
                    ]
                ],
                false
            ]
        ];
    }

    private function buildPluginSetEntryMock(array $data): PluginSetEntry
    {
        /** @var PluginSetEntry|MockObject $pluginSetEntryMock */
        $pluginSetEntryMock = $this->getMockBuilder(PluginSetEntry::class)->getMock();
        $pluginSetEntryMock->pluginId = $data['pluginId'];
        $pluginSetEntryMock->pluginSetId = $data['pluginSetId'];
        $pluginSetEntryMock->createdAt = $data['createdAt'];
        $pluginSetEntryMock->updatedAt = $data['updatedAt'];
        $pluginSetEntryMock->deleted_at = $data['deleted_at'];
        $pluginSetEntryMock->branchName = $data['branchName'];
        $pluginSetEntryMock->position = $data['position'];
        $pluginSetEntryMock->commit = $data['commit'];

        return $pluginSetEntryMock;
    }
}
