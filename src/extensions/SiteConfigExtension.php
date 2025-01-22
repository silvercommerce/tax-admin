<?php

namespace SilverCommerce\TaxAdmin\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\LiteralField;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverStripe\Forms\GridField\GridField;
use SilverCommerce\TaxAdmin\Model\TaxCategory;
use SilverCommerce\TaxAdmin\Forms\GridFieldTaxConfig;

/**
 * Provides additional settings required globally for this module
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package product-catalogue
 */
class SiteConfigExtension extends DataExtension
{
    
    private static $has_many = [
        "TaxRates" => TaxRate::class,
        "TaxCategories" => TaxCategory::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        /** @var SiteConfig */
        $owner = $this->getOwner();
        $config = GridFieldTaxConfig::create();
        $cats = $owner->TaxCategories();
        $rates = $owner->TaxRates();

        // Add config sets
        $fields->addFieldsToTab(
            'Root.Tax',
            [
                GridField::create(
                    'TaxCategories',
                    null,
                    $cats,
                    $config
                ),
                LiteralField::create(
                    "TaxDivider",
                    '<div class="form-group field"></div>'
                ),
                GridField::create(
                    'TaxRates',
                    null,
                    $rates,
                    $config
                )
            ]
        );
    }
}
