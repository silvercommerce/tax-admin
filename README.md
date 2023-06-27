Silverstripe Tax Admin
======================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silvercommerce/tax-admin/badges/quality-score.png?b=1.0)](https://scrutinizer-ci.com/g/silvercommerce/tax-admin/?branch=1.0)
[![Build Status](https://travis-ci.org/silvercommerce/tax-admin.svg?branch=1.0)](https://travis-ci.org/silvercommerce/tax-admin)

Adds ability to create and edit tax rates and categories in
the CMS (via siteconfig).

This allows for flexible tax configurations (meaning that
you can have tax rates for different countries, zones, etc).

You can then map these categories (or rates) to products,
line items (in invoices) etc and use them in your tax
calculations.

## Dependancies

* [SilverStripe Framework](https://github.com/silverstripe/silverstripe-framework)
* [SilverStripe SiteConfig](https://github.com/silverstripe/silverstripe-siteconfig)
* [SilverCommerce GeoZones](https://github.com/silvercommerce/geozones)

## Assigning Taxes to Zones

By default this module integrates with the
[GeoZones](https://github.com/silvercommerce/geozones)
module. This allows you to assign zones to `TaxRate` objects.
You can then use `TaxCategory::getValidRate()` to return the
most appropriate TaxRate for this category, based either on
the provided locale and zone, of the system default. EG:

```php
use SilverCommerce\TaxAdmin\Model\TaxCategory;

// Get the tax category you want
$cateogry = TaxCategory::get()->byID(1);

// Find if we have a valid rate for Gloucestershire in the UK (GB)
$tax_rate = $category->getValidTax("GB", "GLS");
```

## Testing

The tests need a DB. To run them ensure that you have a `.env` file and you have setup some/all of the following

```env
SS_DATABASE_CLASS="MySQLDatabase"
SS_DATABASE_USERNAME="root"
SS_DATABASE_PASSWORD="chooseAPassword"
SS_DATABASE_SERVER="ip_address/localhost"
SS_DATABASE_NAME="yourDbName"
```