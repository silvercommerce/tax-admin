<?php

namespace SilverCommerce\TaxAdmin\Tests;

use NumberFormatter;
use SilverStripe\i18n\i18n;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Security;
use SilverStripe\Core\Config\Config;
use SilverCommerce\GeoZones\Model\Region;
use SilverCommerce\TaxAdmin\PricingExtension;
use SilverCommerce\TaxAdmin\Tests\Model\TestProduct;

/**
 * Test functionality of postage extension
 *
 */
class PricingExtensionTest extends SapphireTest
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

    public function setUp()
    {
        parent::setUp();
        Config::inst()->set(Region::class, "create_on_build", false);
        
        // Setup default locale
        i18n::set_locale("en_GB");
        $member = Security::getCurrentUser();
        $member->Locale = "en_GB";
    }

    public function testFilterExtensionResults()
    {
        $product = $this->objFromFixture(TestProduct::class, 'product1');

        $null = $product->filterExtensionResults(null);
        $string = $product->filterExtensionResults("One");
        $list = $product->filterExtensionResults(["One", "Two", "Three"]);

        $this->assertNull($null);
        $this->assertNull($string);
        $this->assertEquals("One", $list);
    }

    public function testGetShowPriceWithTax()
    {
        $curr = PricingExtension::config()->get('default_price_with_tax');
        $product = $this->objFromFixture(TestProduct::class, 'product1');
    
        PricingExtension::config()->set('default_price_with_tax', false);
        $this->assertFalse($product->getShowPriceWithTax());

        PricingExtension::config()->set('default_price_with_tax', true);
        $this->assertTrue($product->getShowPriceWithTax());

        PricingExtension::config()->set('default_price_with_tax', $curr);
    }

    public function testGetShowTaxString()
    {
        $curr = PricingExtension::config()->get('default_tax_string');
        $product = $this->objFromFixture(TestProduct::class, 'product1');

        PricingExtension::config()->set('default_tax_string', false);
        $this->assertFalse($product->getShowTaxString());

        PricingExtension::config()->set('default_tax_string', true);
        $this->assertTrue($product->getShowTaxString());

        PricingExtension::config()->set('default_tax_string', $curr);
    }

    public function testGetPrecision()
    {
        $curr = PricingExtension::config()->get('default_precision');
        $product = $this->objFromFixture(TestProduct::class, 'product1');

        PricingExtension::config()->set('default_precision', 2);
        $this->assertEquals(2, $product->getPrecision());

        PricingExtension::config()->set('default_precision', 4);
        $this->assertEquals(4, $product->getPrecision());

        PricingExtension::config()->set('default_precision', $curr);
    }

    public function testGetFormatter()
    {
        $product = $this->objFromFixture(TestProduct::class, 'product1');

        $this->assertInstanceOf(NumberFormatter::class, $product->getFormatter());
    }

    public function testGetCurrencySymbol()
    {
        $locale = i18n::get_locale();
        $product = $this->objFromFixture(TestProduct::class, 'product1');

        i18n::set_locale('en_GB');
        $this->assertEquals("£", $product->getCurrencySymbol());

        i18n::set_locale('en_US');
        $this->assertEquals("$", $product->getCurrencySymbol());

        i18n::set_locale('en_DE');
        $this->assertEquals("€", $product->getCurrencySymbol());

        i18n::set_locale($locale);
    }

    public function testGetCurrency()
    {
        $locale = i18n::get_locale();
        $product = $this->objFromFixture(TestProduct::class, 'product1');

        i18n::set_locale('en_GB');
        $this->assertEquals("GBP", $product->getCurrency());

        i18n::set_locale('en_US');
        $this->assertEquals("USD", $product->getCurrency());

        i18n::set_locale('en_DE');
        $this->assertEquals("EUR", $product->getCurrency());

        i18n::set_locale($locale);
    }

    public function testGetNoTaxPrice()
    {
        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product2');
        $p_three = $this->objFromFixture(TestProduct::class, 'product8');

        $this->assertEquals('16.66', $p_one->NoTaxPrice);
        $this->assertEquals('83.29', $p_two->NoTaxPrice);
        $this->assertEquals('16.625', $p_three->NoTaxPrice);
    }

    public function testGetRoundedNoTaxPrice()
    {
        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product2');
        $p_three = $this->objFromFixture(TestProduct::class, 'product5');

        $this->assertEquals('16.66', $p_one->NoTaxPrice);
        $this->assertEquals('83.29', $p_two->NoTaxPrice);
        $this->assertEquals('49.99', $p_three->NoTaxPrice);
    }

    public function testGetTaxRate()
    {
        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product3');

        $this->assertEquals(20, $p_one->getTaxRate()->Rate);
        $this->assertEquals(5, $p_two->getTaxRate()->Rate);
    }

    public function testGetTaxPercentage()
    {
        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product3');
        $p_three = $this->objFromFixture(TestProduct::class, 'product4');

        $this->assertEquals(20, $p_one->getTaxPercentage());
        $this->assertEquals(5, $p_two->getTaxPercentage());
        $this->assertEquals(0, $p_three->getTaxPercentage());
    }

    public function testGetTaxAmount()
    {
        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product2');
        $p_three = $this->objFromFixture(TestProduct::class, 'product3');
        $p_four = $this->objFromFixture(TestProduct::class, 'product4');
        $p_five = $this->objFromFixture(TestProduct::class, 'product5');
        $p_six = $this->objFromFixture(TestProduct::class, 'product6');
        $p_seven = $this->objFromFixture(TestProduct::class, 'product7');
        $p_eight = $this->objFromFixture(TestProduct::class, 'product8');
        $p_nine = $this->objFromFixture(TestProduct::class, 'product9');
        $p_ten = $this->objFromFixture(TestProduct::class, 'product10');

        $this->assertEquals(3.332, $p_one->getTaxAmount());
        $this->assertEquals(16.658, $p_two->getTaxAmount());
        $this->assertEquals(0.625, $p_three->getTaxAmount());
        $this->assertEquals(0, $p_four->getTaxAmount());
        $this->assertEquals(9.998, $p_five->getTaxAmount());
        $this->assertEquals(24.834, $p_six->getTaxAmount());
        $this->assertEquals(2.498, $p_seven->getTaxAmount());
        $this->assertEquals(3.325, $p_eight->getTaxAmount());
        $this->assertEquals(2.825, $p_nine->getTaxAmount());
        $this->assertEquals(24.998, $p_ten->getTaxAmount());
    }

    public function testGetRoundedTaxAmount()
    {
        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product2');
        $p_three = $this->objFromFixture(TestProduct::class, 'product3');
        $p_four = $this->objFromFixture(TestProduct::class, 'product4');
        $p_five = $this->objFromFixture(TestProduct::class, 'product5');
        $p_six = $this->objFromFixture(TestProduct::class, 'product6');
        $p_seven = $this->objFromFixture(TestProduct::class, 'product7');
        $p_eight = $this->objFromFixture(TestProduct::class, 'product8');
        $p_nine = $this->objFromFixture(TestProduct::class, 'product9');
        $p_ten = $this->objFromFixture(TestProduct::class, 'product10');

        $this->assertEquals(3.33, $p_one->getRoundedTaxAmount());
        $this->assertEquals(16.66, $p_two->getRoundedTaxAmount());
        $this->assertEquals(0.63, $p_three->getRoundedTaxAmount());
        $this->assertEquals(0, $p_four->getRoundedTaxAmount());
        $this->assertEquals(10, $p_five->getRoundedTaxAmount());
        $this->assertEquals(24.83, $p_six->getRoundedTaxAmount());
        $this->assertEquals(2.5, $p_seven->getRoundedTaxAmount());
        $this->assertEquals(3.33, $p_eight->getRoundedTaxAmount());
        $this->assertEquals(2.83, $p_nine->getRoundedTaxAmount());
        $this->assertEquals(25, $p_ten->getRoundedTaxAmount());
    }

    public function testGetPriceAndTax()
    {
        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product2');
        $p_three = $this->objFromFixture(TestProduct::class, 'product3');
        $p_four = $this->objFromFixture(TestProduct::class, 'product4');
        $p_five = $this->objFromFixture(TestProduct::class, 'product5');
        $p_six = $this->objFromFixture(TestProduct::class, 'product6');
        $p_seven = $this->objFromFixture(TestProduct::class, 'product7');
        $p_eight = $this->objFromFixture(TestProduct::class, 'product8');
        $p_nine = $this->objFromFixture(TestProduct::class, 'product9');
        $p_ten = $this->objFromFixture(TestProduct::class, 'product10');

        $this->assertEquals(19.992, $p_one->getPriceAndTax());
        $this->assertEquals(99.948, $p_two->getPriceAndTax());
        $this->assertEquals(13.125, $p_three->getPriceAndTax());
        $this->assertEquals(12.5, $p_four->getPriceAndTax());
        $this->assertEquals(59.988, $p_five->getPriceAndTax());
        $this->assertEquals(149.004, $p_six->getPriceAndTax());
        $this->assertEquals(14.988, $p_seven->getPriceAndTax());
        $this->assertEquals(19.95, $p_eight->getPriceAndTax());
        $this->assertEquals(16.95, $p_nine->getPriceAndTax());
        $this->assertEquals(149.988, $p_ten->getPriceAndTax());
    }

    public function testGetFormattedPrice()
    {
        $whitespace = "\xc2\xa0";
        $locale = i18n::get_locale();

        $p_one = $this->objFromFixture(TestProduct::class, 'product1');
        $p_two = $this->objFromFixture(TestProduct::class, 'product6');
        $p_three = $this->objFromFixture(TestProduct::class, 'product8');
        $p_four = $this->objFromFixture(TestProduct::class, 'product9');
        $p_five = $this->objFromFixture(TestProduct::class, 'product10');

        i18n::set_locale('en_GB');
        $this->assertEquals("£16.66", $p_one->getFormattedPrice());
        $this->assertEquals("£124.17", $p_two->getFormattedPrice());
        $this->assertEquals("£16.62", $p_three->getFormattedPrice());
        $this->assertEquals("£14.12", $p_four->getFormattedPrice());
        $this->assertEquals("£124.99", $p_five->getFormattedPrice());

        i18n::set_locale('en_US');
        $this->assertEquals("$16.66", $p_one->getFormattedPrice());

        i18n::set_locale('de_DE');
        $this->assertEquals(
            '16,66 €',
            str_replace($whitespace, ' ', $p_one->getFormattedPrice())
        );

        i18n::set_locale('en_GB');
        $this->assertEquals("£19.99", $p_one->getFormattedPrice(true));
        $this->assertEquals("£149.00", $p_two->getFormattedPrice(true));
        $this->assertEquals("£19.95", $p_three->getFormattedPrice(true));
        $this->assertEquals("£16.95", $p_four->getFormattedPrice(true));
        $this->assertEquals("£149.99", $p_five->getFormattedPrice(true));

        i18n::set_locale('en_US');
        $this->assertEquals("$19.99", $p_one->getFormattedPrice(true));

        i18n::set_locale('de_DE');
        $this->assertEquals(
            '19,99 €',
            str_replace($whitespace, ' ', $p_one->getFormattedPrice(true))
        );

        i18n::set_locale($locale);
    }
}
