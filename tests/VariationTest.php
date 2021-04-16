<?php

use BoldMinded\Experiments\Services\Variation;
use PHPUnit\Framework\TestCase;

class VariationTest extends TestCase
{
    public function testWithDefaultOptions()
    {
        $variation = (new Variation())->setOptions([], [Variation::QUERY_PARAM_NAME => 1]);

        $this->assertTrue($variation->shouldShowContent(1));

        $variation = (new Variation())->setOptions([], [Variation::QUERY_PARAM_NAME => 2]);

        $this->assertTrue($variation->shouldShowContent(2));

        $variation = (new Variation())->setOptions([], [Variation::QUERY_PARAM_NAME => 3]);

        $this->assertTrue($variation->shouldShowContent(3));

        $variation = (new Variation())->setOptions([], [Variation::QUERY_PARAM_NAME => 4]);

        $this->assertFalse($variation->shouldShowContent(1));

        $variation = (new Variation())->setOptions([], []);

        $this->assertFalse($variation->shouldShowContent(1));
    }

    public function testRandomization()
    {
        $variation = (new Variation())->setOptions(['randomize' => true]);

        $this->assertTrue(in_array($variation->getChosen(), [0, 1]));

        $variation = (new Variation())->setOptions(['randomize' => true]);

        $this->assertFalse(in_array($variation->getChosen(), [2, 3, 99]));
    }

    public function testAnyVariation()
    {
        $variation = (new Variation())->setOptions([], [Variation::QUERY_PARAM_NAME => 2]);

        $this->assertTrue($variation->shouldShowContent(99));
    }

    public function testDefaultControl()
    {
        $variation = (new Variation());

        $this->assertTrue($variation->shouldShowContent(0));

        $variation = (new Variation(['default' => 2]));

        $this->assertTrue($variation->shouldShowContent(2));
    }

    public function testConfigFromEE()
    {
        $variation = (new Variation())
            ->setOptionsFromConfig(['queryParameterName' => 'foo'])
            ->setOptions();

        $this->assertEquals('foo', $variation->getOption('queryParameterName'));
    }
}
