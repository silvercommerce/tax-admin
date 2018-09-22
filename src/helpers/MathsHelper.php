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

    const ROUND_DEFAULT = 1;

    const ROUND_UP = 2;

    const ROUND_DOWN = 3;

    /**
     * The default rounding used by this class
     */
    private static $default_round = self::ROUND_DEFAULT;

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
        return self::round($value, $places, self::ROUND_UP);
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
        return self::round($value, $places, self::ROUND_DOWN);
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
    public static function round($value, $places = 0, $type = null)
    {
        if (!isset($type)) {
            $type = Config::inst()->get(self::class, "default_round");
        }

        $offset = 0;

        // If we are rounding to decimals get a more granular number.
        if ($places !== 0 && $type !== self::ROUND_DEFAULT) {
            if ($type == self::ROUND_DOWN) {
                $offset = -0.45;
            } elseif ($type == self::ROUND_UP) {
                $offset = 0.45;
            }
            $offset /= pow(10, $places) + 1;
        }

        // if we are rounding to whole numbers and forcing up
        // down, use ceil/floor
        if ($places == 0 && $type == self::ROUND_UP) {
            $return = ceil($value);
        } elseif ($places == 0 && $type == self::ROUND_DOWN) {
            $return = floor($value);
        } else {
            $return = round(
                $value + $offset,
                $places
            );
        }

        return $return;
    }
}
