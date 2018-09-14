<?php

namespace Findologic\Tests\Api\Response;

use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
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

    public function setUp()
    {
        $this->filterParser = $this->getMockBuilder(FiltersParser::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
    }

    public function testParse()
    {
        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods(null)->getMock();
        /** @var ResponseParser|MockObject $responseParserMock */
        $responseParserMock = $this->getResponseParserMock(['createResponseObject']);
        $responseParserMock->expects($this->any())->method('createResponseObject')->willReturn($responseMock);

        $results = $responseParserMock->parse($this->getResponse());
        $this->assertEquals(3, $results->getResultsCount());
    }

    /**
     * @param array|null $methods
     * @return ResponseParser|MockObject
     */
    protected function getResponseParserMock($methods = null)
    {
        $responseParserMock = $this->getMockBuilder(ResponseParser::class)
            ->setConstructorArgs([
                'filterParser' => $this->filterParser,
                'loggerFactory' => $this->loggerFactory
            ])
            ->setMethods($methods);

        return $responseParserMock->getMock();
    }

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
    </filters>
</searchResult>';
    }
}