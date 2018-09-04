<?php

namespace SilverCommerce\TaxAdmin\Helpers;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

/**
 * Simple helper class to provide generic functions to help with
 * maths functions
 */
class MathsHelper
{
    use Injectable;
    use Configurable;

    /**
     * Round all numbers down globally
     *
     * @var boolean
     */
    private static $round_down = true;

    /**
     * Rounds up a float to a specified number of decimal places
     * (basically acts like ceil() but allows for decimal places)
     *
     * @param float $value Float to round up
     * @param int $places the number of decimal places to round to
     *
     * @return float
     */
    public static function round_up($value, $places = 0)
    {
        return self::round($value, $places, false);
    }

    /**
     * Rounds up a float to a specified number of decimal places
     * (basically acts like ceil() but allows for decimal places)
     *
     * @param float $value Float to round up
     * @param int $places the number of decimal places to round to
     *
     * @return float
     */
    public static function round_down($value, $places = 0)
    {
        return self::round($value, $places, true);
    }

    /**
     * Round the provided value to the defined number of places in the direction
     * provided (up or down).
     *
     * @param float   $value  The value we want to round
     * @param int     $places The number of decimal places to round to
     * @param boolean $down   Do we round down? If false value will be rounded up
     * 
     * @return float
     */
    public static function round($value, $places = 0, $down = null, $negatives = false)
    {
        if (empty($down)) {
            $down = Config::inst()->get(self::class, "round_down");
        }

        $offset = 0;

        // If we are rounding to decimals get a more granular number.
        if ($places !== 0) {
            if ($down) {
                $offset = -0.45;
            } else {
                $offset = 0.45;
            }
            $offset /= pow(10, $places);
        }

        $return = round(
            $value + $offset,
            $places,
            PHP_ROUND_HALF_UP
        );

        return $return;
    }
}
