<?php

namespace SilverCommerce\TaxAdmin\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Config\Config;
use SilverCommerce\GeoZones\Model\Region;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverCommerce\TaxAdmin\Tests\Model\TestProduct;

/**
 * Test functionality of postage extension
 *
 */
class TaxRateTest extends SapphireTest
{

    protected static $fixture_file = 'TaxData.yml';

    /**
     * Setup test only objects
     *
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class
    ];

    public function setUp(): void
    {
        parent::setUp();
        Config::inst()->set(Region::class, "create_on_build", false);
    }

    /**
     * Test that Tax Rate returns an accurate list
     */
    public function testZonesList()
    {
        $obj = $this->objFromFixture(TaxRate::class, "vat");

        // Test default location
        $this->assertTrue(strpos($obj->ZonesList, "UK") !== false);
        $this->assertTrue(strpos($obj->ZonesList, "Germany") !== false);
    }
}
