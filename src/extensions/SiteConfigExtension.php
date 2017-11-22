<?php

namespace SilverCommerce\TaxAdmin\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverCommerce\TaxAdmin\Model\TaxCategory;

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
        // Add config sets
        $fields->addFieldsToTab(
            'Root.Tax',
            [
                GridField::create(
                    'TaxRates',
                    null,
                    $this->owner->TaxRates()
                )->setConfig(new GridFieldConfig_RelationEditor()),
                LiteralField::create(
                    "TaxDivider",
                    '<div class="form-group field"></div>'
                ),
                GridField::create(
                    'TaxCategories',
                    null,
                    $this->owner->TaxCategories()
                )->setConfig(new GridFieldConfig_RelationEditor())
            ]
        );
    }
}
