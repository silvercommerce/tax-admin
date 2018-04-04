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
class TaxCategoryTest extends SapphireTest
{

    protected static $fixture_file = 'TaxData.yml';

	public function setUp()
    {
		parent::setUp();
        Config::inst()->set(Region::class, "create_on_build", false);
	}

    /**
     * Test category valid tax retursn the correct value
     */
    public function testValidTax()
    {
        $obj = $this->objFromFixture(TaxCategory::class, "uk");

        // Test default location
        $this->assertEquals("VAT", $obj->ValidTax()->Title);

        // Test VAT location
        $this->assertEquals("VAT", $obj->ValidTax("GB")->Title);

        // Test VAT location for country and region
        $this->assertEquals("VAT", $obj->ValidTax("GB", "GLS")->Title);
        
        // Test reduced location
        $this->assertEquals("reduced", $obj->ValidTax("US")->Title);
        
        // Test location for valid country and invalid region
        $this->assertNull($obj->ValidTax("GB", "ABE"));

        // Test unavailable location
        $this->assertNull($obj->ValidTax("ES"));
    }
}