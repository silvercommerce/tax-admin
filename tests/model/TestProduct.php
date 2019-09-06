<?php

namespace SilverCommerce\TaxAdmin\Tests\Model;

use SilverCommerce\TaxAdmin\PricingExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class TestProduct extends DataObject implements TestOnly
{
    private static $db = [
        "Title" => "Varchar",
        "StockID" => "Varchar"
    ];

    private static $extensions = [
        PricingExtension::class
    ];
}
