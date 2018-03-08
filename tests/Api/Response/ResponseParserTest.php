<?php

namespace Findologic\PluginPlentymarketsApi\Tests;

use Findologic\PluginPlentymarketsApi\Api\Response\ResponseParser;

/**
 * Class ResponseParserTest
 * @package Findologic\PluginPlentymarketsApi\Tests
 */
class ResponseParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $responseParserMock = $this->getMockBuilder(ResponseParser::class)->disableOriginalConstructor()->getMock();
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
        <count>2</count>
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
                    <name>Firma Müller</name>
                    <weight>0.863121</weight>
                    <frequency>2</frequency>
                    <image>http://www.example.com/vendor/firma_muller.jpg</image>
                </item>
            </items>
        </filter>
        <filter>
            <select>single</select>
            <name>price</name>
            <items>
                <item>
                    <name>1.23 - 6.66</name>
                    <weight>0.863121</weight>
                    <frequency>2</frequency>
                    <parameters>
                        <min>1.23</min>
                        <max>6.66</max>
                    </parameters>
                </item>
                <item>
                    <name>6.66 - 8</name>
                    <weight>0.591673</weight>
                    <frequency>1</frequency>
                    <parameters>
                        <min>6.66</min>
                        <max>8</max>
                    </parameters>
                </item>
            </items>
        </filter>
    </filters>
</searchResult>';
    }
}