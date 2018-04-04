<?php

namespace SilverCommerce\TaxAdmin\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Config\Config;
use SilverCommerce\GeoZones\Model\Region;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverCommerce\TaxAdmin\Model\TaxCategory;

/**
 * Test functionality of postage extension
 *
 */
class TaxRateTest extends SapphireTest
{

    protected static $fixture_file = 'TaxData.yml';

    public function setUp()
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
        $this->assertEquals("UK, Germany", $obj->ZonesList);
    }
}
