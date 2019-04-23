<?php

namespace Findologic\Tests\Api\Response\Parser;

use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Api\Response\Response;
use Findologic\Api\Services\Image;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FiltersParserTest
 * @package Findologic\Tests\Api\Response\Parser
 */
class FiltersParserTest extends TestCase
{
    /**
     * @var Image|MockObject
     */
    protected $imageService;

    /**
     * @var LibraryCallContract|MockObject
     */
    protected $libraryCallContract;

    public function setUp()
    {
        $this->libraryCallContract = $this->getMockBuilder(LibraryCallContract::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->imageService = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    public function testParse()
    {
        /** @var FiltersParser|MockObject $filtersParserMock */
        $filtersParserMock = $this->getFiltersParserMock();

        $response = $this->getResponse();
        $results = $filtersParserMock->parse($response);

        $this->assertNotEmpty($results);

        foreach ($results as $index => $result) {
            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('name', $result);
            $this->assertArrayHasKey('select', $result);
            $this->assertArrayHasKey('type', $result);
            $this->assertArrayHasKey('values', $result);

            $this->assertEquals($result['id'], $response->filters->filter[$index]->name);
            $this->assertEquals($result['name'], $response->filters->filter[$index]->display);
            $this->assertEquals($result['select'], $response->filters->filter[$index]->select);
            $this->assertEquals($result['type'], $response->filters->filter[$index]->type);
            $this->assertCount(count($result['values']), $response->filters->filter[$index]->items->item);
        }
    }

    public function testNoFiltersResponse()
    {
        /** @var FiltersParser|MockObject $filtersParserMock */
        $filtersParserMock = $this->getFiltersParserMock();

        $results = $filtersParserMock->parse($this->getNoFiltersResponse());
        $this->assertEmpty($results);
    }

    /**
     * @param array $methods
     * @return MockObject
     */
    protected function getFiltersParserMock($methods = [])
    {
        $methods[] = 'createResponseObject';

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods(null)->getMock();

        $filtersParserMock = $this->getMockBuilder(FiltersParser::class)
            ->setConstructorArgs([
                'libraryCallContract' => $this->libraryCallContract,
                'imageService' => $this->imageService
            ])
            ->setMethods($methods)->getMock();
        $filtersParserMock->expects($this->any())->method('createResponseObject')->willReturn($responseMock);

        return $filtersParserMock;
    }

    /**
     * @return \SimpleXMLElement
     */
    protected function getNoFiltersResponse()
    {
        return simplexml_load_string('<?xml version="1.0"?>
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
    <filters></filters>
</searchResult>');
    }

    /**
     * @return \SimpleXMLElement
     */
    protected function getResponse()
    {
        return simplexml_load_string('<?xml version="1.0"?>
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
            <display>Kategorie</display>
            <select>single</select>
            <type>select</type>
            <items>
                <item>
                    <name>Wohnzimmer</name>
                    <weight>0.30303025245667</weight>
                    <frequency>28</frequency>
                    <items>
                        <item>
                            <name>Sessel &amp; Hocker</name>
                            <weight>0.96969699859619</weight>
                            <frequency>17</frequency>
                        </item>
                        <item>
                            <name>Sofas</name>
                            <weight>0.66666668653488</weight>
                            <frequency>11</frequency>
                        </item>
                    </items>
                </item>
                <item>
                    <name>Arbeitszimmer &amp; Büro</name>
                    <weight>0.36363637447357</weight>
                    <frequency>6</frequency>
                    <items>
                        <item>
                            <name>Bürostühle</name>
                            <weight>0.36363637447357</weight>
                            <frequency>6</frequency>
                        </item>
                    </items>
                </item>
            </items>
        </filter>
        <filter>
            <name>vendor</name>
            <select>multiple</select>
            <type>image</type>
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
                <item>
                    <name>A &amp; C Design</name>
                    <weight>0.72727274894714</weight>
                    <frequency>21</frequency>
                    <image>/vendor/a_amp_c_design.jpg</image>
                </item>
                <item>
                    <name>H Manufacturer</name>
                    <weight>0.52727274894714</weight>
                    <frequency>25</frequency>
                    <image>https://test.com/vendor/a_amp_c_design.jpg</image>
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
            <name>price-text</name>
            <display>Preis</display>
            <select>single</select>
            <type>text</type>
            <items>
                <item>
                </item>
            </items>
        </filter>
        <filter>
            <name>color</name>
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
</searchResult>');
    }
}