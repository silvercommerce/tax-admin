<?php

namespace SilverCommerce\TaxAdmin\Tests;

use SilverStripe\i18n\i18n;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Security;
use SilverStripe\Core\Config\Config;
use SilverCommerce\GeoZones\Model\Region;
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
        
        // Setup default locale
        i18n::set_locale("en_GB");
        $member = Security::getCurrentUser();
        $member->Locale = "en_GB";
    }

    /**
     * Test category valid tax retursn the correct value
     */
    public function testValidTax()
    {
        $obj = $this->objFromFixture(TaxCategory::class, "uk");

        // Test default location (when logged in)
        $this->assertEquals("VAT", $obj->ValidTax()->Title);

        // Test default location (when not logged in)
        Security::setCurrentUser(null);
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
