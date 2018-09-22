<?php

namespace SilverCommerce\TaxAdmin\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverCommerce\TaxAdmin\Helpers\MathsHelper;

/**
 * Test functionality of postage extension
 *
 */
class MathsHelperTest extends SapphireTest
{
    protected $price_one = 82.83;

    protected $price_two = 49.62;

    protected $price_three = 28.34;

    /**
     * Quickly calculate tax at 20%
     *
     * @return float
     */
    protected function calculateTax($value)
    {
        return ($value / 100 * 20);
    }

    /**
     * Test that standard rounding works to different decimals.
     * 
     */
    public function testRound()
    {
        $this->assertEquals(
            17,
            MathsHelper::round($this->calculateTax($this->price_one))
        );

        $this->assertEquals(
            16.6,
            MathsHelper::round($this->calculateTax($this->price_one), 1)
        );

        $this->assertEquals(
            16.57,
            MathsHelper::round($this->calculateTax($this->price_one), 2)
        );

        $this->assertEquals(
            16.566,
            MathsHelper::round($this->calculateTax($this->price_one), 3)
        );

        $this->assertEquals(
            10,
            MathsHelper::round($this->calculateTax($this->price_two))
        );

        $this->assertEquals(
            9.9,
            MathsHelper::round($this->calculateTax($this->price_two), 1)
        );

        $this->assertEquals(
            9.92,
            MathsHelper::round($this->calculateTax($this->price_two), 2)
        );

        $this->assertEquals(
            9.924,
            MathsHelper::round($this->calculateTax($this->price_two), 3)
        );

        $this->assertEquals(
            6,
            MathsHelper::round($this->calculateTax($this->price_three))
        );

        $this->assertEquals(
            5.7,
            MathsHelper::round($this->calculateTax($this->price_three), 1)
        );

        $this->assertEquals(
            5.67,
            MathsHelper::round($this->calculateTax($this->price_three), 2)
        );

        $this->assertEquals(
            5.668,
            MathsHelper::round($this->calculateTax($this->price_three), 3)
        );
    }

    /**
     * Test forcing rounding down
     *
     */
    public function testRoundDown()
    {
        $this->assertEquals(
            16,
            MathsHelper::round_down($this->calculateTax($this->price_one))
        );

        $this->assertEquals(
            16.5,
            MathsHelper::round_down($this->calculateTax($this->price_one), 1)
        );

        $this->assertEquals(
            16.56,
            MathsHelper::round_down($this->calculateTax($this->price_one), 2)
        );

        $this->assertEquals(
            16.566,
            MathsHelper::round_down($this->calculateTax($this->price_one), 3)
        );

        $this->assertEquals(
            9,
            MathsHelper::round_down($this->calculateTax($this->price_two))
        );

        $this->assertEquals(
            9.9,
            MathsHelper::round_down($this->calculateTax($this->price_two), 1)
        );

        $this->assertEquals(
            9.92,
            MathsHelper::round_down($this->calculateTax($this->price_two), 2)
        );

        $this->assertEquals(
            9.924,
            MathsHelper::round_down($this->calculateTax($this->price_two), 3)
        );

        $this->assertEquals(
            5,
            MathsHelper::round_down($this->calculateTax($this->price_three))
        );

        $this->assertEquals(
            5.6,
            MathsHelper::round_down($this->calculateTax($this->price_three), 1)
        );

        $this->assertEquals(
            5.66,
            MathsHelper::round_down($this->calculateTax($this->price_three), 2)
        );

        $this->assertEquals(
            5.668,
            MathsHelper::round_down($this->calculateTax($this->price_three), 3)
        );
    }

    /**
     * Test forcing rounding up.
     * 
     */
    public function testRoundUp()
    {
        $this->assertEquals(
            17,
            MathsHelper::round_up($this->calculateTax($this->price_one))
        );

        $this->assertEquals(
            16.6,
            MathsHelper::round_up($this->calculateTax($this->price_one), 1)
        );

        $this->assertEquals(
            16.57,
            MathsHelper::round_up($this->calculateTax($this->price_one), 2)
        );

        $this->assertEquals(
            16.566,
            MathsHelper::round_up($this->calculateTax($this->price_one), 3)
        );

        $this->assertEquals(
            10,
            MathsHelper::round_up($this->calculateTax($this->price_two))
        );

        $this->assertEquals(
            10,
            MathsHelper::round_up($this->calculateTax($this->price_two), 1)
        );

        $this->assertEquals(
            9.93,
            MathsHelper::round_up($this->calculateTax($this->price_two), 2)
        );

        $this->assertEquals(
            9.924,
            MathsHelper::round_up($this->calculateTax($this->price_two), 3)
        );

        $this->assertEquals(
            6,
            MathsHelper::round_up($this->calculateTax($this->price_three))
        );

        $this->assertEquals(
            5.7,
            MathsHelper::round_up($this->calculateTax($this->price_three), 1)
        );

        $this->assertEquals(
            5.67,
            MathsHelper::round_up($this->calculateTax($this->price_three), 2)
        );

        $this->assertEquals(
            5.668,
            MathsHelper::round_up($this->calculateTax($this->price_three), 3)
        );
    }
}
