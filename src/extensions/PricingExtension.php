<?php

namespace SilverCommerce\TaxAdmin;

use NumberFormatter;
use SilverStripe\i18n\i18n;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ReadonlyField;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverStripe\Core\Config\Configurable;
use SilverCommerce\TaxAdmin\Model\TaxCategory;
use SilverCommerce\TaxAdmin\Helpers\MathsHelper;

/**
 * Extension that handles the grind work of calculating Tax prices and rendering
 * prices for an extended object.
 */
class PricingExtension extends DataExtension
{
    use Configurable;

    /**
     * Default behaviour for price with tax (if current instance not set)
     *
     * @var boolean
     */
    private static $default_price_with_tax = false;

    /**
     * Default behaviour for adding the tax string to the rendered currency.
     *
     * @var boolean
     */
    private static $default_tax_string = false;

    /**
     * Default decimal precision used for rounding
     *
     * @var int
     */
    private static $default_precision = 2;


    private static $db = [
        'BasePrice' => 'Decimal'
    ];

    private static $has_one = [
        'TaxCategory' => TaxCategory::class,
        'TaxRate' => TaxRate::class
    ];

    private static $casting = [
        'CurrencySymbol'    => 'Varchar(1)',
        'Currency'          => 'Varchar(3)',
        "NoTaxPrice"        => "Decimal",
        "TaxAmount"         => "Decimal",
        "TaxPercentage"     => "Decimal",
        "PriceAndTax"       => "Decimal",
        'TaxString'         => 'Varchar',
        'RoundedNoTaxPrice' => 'Decimal',
        'RoundedTaxAmount'  => 'Decimal',
        'RoundedPriceAndTax'=> 'Decimal',
        'ShowPriceWithTax'  => 'Boolean',
        'ShowTaxString'     => 'Boolean',
        'FormattedPrice'    => 'Varchar',
        'NicePrice'         => 'HTMLText'
    ];

    private static $field_labels = [
        'BasePrice' => 'Price'
    ];

    /**
     * Filter the results returned by an extension
     *
     * @param mixed $results Possible results
     *
     * @return mixed
     */
    public function filterExtensionResults($results)
    {
        if (!empty($results) && is_array($results)) {
            $results = array_filter(
                $results,
                function ($v) {
                    return !is_null($v);
                }
            );
            if (is_array($results) && count($results) > 0) {
                return $results[0];
            }
        }

        return;
    }

    /**
     * Get should this field automatically show the price including TAX?
     *
     * @return boolean
     */
    public function getShowPriceWithTax()
    {
        return $this->config()->get('default_price_with_tax');
    }


    /**
     * Get if this field should add a "Tax String" (EG Includes VAT) to the rendered
     * currency?
     *
     * @return boolean|null
     */
    public function getShowTaxString()
    {
        return $this->config()->get('default_tax_string');
    }

    /**
     * Get current decimal precision for rounding
     *
     * @return int
     */
    public function getPrecision()
    {
        return $this->config()->get('default_precision');
    }

    /**
     * Return the currently available locale
     * 
     * @return string 
     */
    public function getLocale()
    {
        return i18n::get_locale();
    }

    /**
     * Get currency formatter
     *
     * @return NumberFormatter
     */
    public function getFormatter()
    {
        return NumberFormatter::create(
            $this->getOwner()->getLocale(),
            NumberFormatter::CURRENCY
        );
    }

    /**
     * Get a currency symbol from the current site local
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this
            ->getOwner()
            ->getFormatter()
            ->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }

    /**
     * Get ISO 4217 currency code from curent locale
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this
            ->getOwner()
            ->getFormatter()
            ->getTextAttribute(NumberFormatter::CURRENCY_CODE);
    }

    /**
     * Shortcut to get the price of this product without tax
     *
     * @return float
     */
    public function getNoTaxPrice()
    {
        $price = $this->getOwner()->BasePrice;
        $result = $this->getOwner()->filterExtensionResults(
            $this->getOwner()->extend("updateNoTaxPrice", $price)
        );

        if (!empty($result)) {
            return $result;
        }

        return $price;
    }

    /**
     * Get the current amount rounded to the desired precision
     *
     * @return float
     */
    public function getRoundedNoTaxPrice()
    {
        return number_format(
            $this->getOwner()->BasePrice,
            $this->getOwner()->getPrecision()
        );
    }

    /**
     * Find a tax rate based on the selected ID, or revert to using the valid tax
     * from the current category
     *
     * @return \SilverCommerce\TaxAdmin\Model\TaxRate
     */
    public function getTaxRate()
    {
        $tax = TaxRate::get()->byID($this->getOwner()->TaxRateID);

        // If no tax explicity set, try to get from category
        if (empty($tax)) {
            $category = TaxCategory::get()->byID($this->getOwner()->TaxCategoryID);

            $tax = (!empty($category)) ? $category->ValidTax() : null ;
        }

        if (empty($tax)) {
            $tax = TaxRate::create();
            $tax->ID = -1;
        }

        return $tax;
    }

    /**
     * Get the percentage tax rate assotiated with this field
     *
     * @return float
     */
    public function getTaxPercentage()
    {
        $percent = $this->getOwner()->getTaxRate()->Rate;

        $result = $this->getOwner()->filterExtensionResults(
            $this->getOwner()->extend("updateTaxPercentage", $percent)
        );

        if (!empty($result)) {
            return $result;
        }

        return $percent;
    }

    /**
     * Get a final tax amount for this object. You can extend this
     * method using "UpdateTax" allowing third party modules to alter
     * tax amounts dynamically.
     *
     * @return float
     */
    public function getTaxAmount()
    {
        if (!$this->getOwner()->exists()) {
            return 0;
        }

        // Round using default rounding defined on MathsHelper
        $tax = MathsHelper::round(
            ($this->getOwner()->BasePrice / 100) * $this->getOwner()->TaxPercentage,
            4
        );

        $result = $this->getOwner()->filterExtensionResults(
            $this->getOwner()->extend("updateTaxAmount", $tax)
        );

        if (!empty($result)) {
            return $result;
        }

        return $tax;
    }

    /**
     * Get the tax amount rounded to the desired precision
     *
     * @return float
     */
    public function getRoundedTaxAmount()
    {
        return number_format(
            $this->getOwner()->TaxAmount,
            $this->getPrecision()
        );
    }

    /**
     * Get the Tax Rate object applied to this product
     *
     * @return float
     */
    public function getPriceAndTax()
    {
        $notax = $this->getOwner()->NoTaxPrice;
        $tax = $this->getOwner()->TaxAmount;
        $price = $notax + $tax;

        $result = $this->getOwner()->filterExtensionResults(
            $this->getOwner()->extend("updatePriceAndTax", $price)
        );

        if (!empty($result)) {
            return $result;
        }

        return $price;
    }

    /**
     * Get the price and tax amount rounded to the desired precision
     *
     * @return float
     */
    public function getRoundedPriceAndTax()
    {
        $amount = $this->getOwner()->RoundedNoTaxPrice;
        $tax = $this->getOwner()->RoundedTaxAmount;
        $price = $amount + $tax;

        $result = $this->getOwner()->filterExtensionResults(
            $this->getOwner()->extend("updateRoundedPriceAndTax", $price)
        );

        if (!empty($result)) {
            return $result;
        }

        return $price;
    }

    /**
     * Generate a string to go with the the product price. We can
     * overwrite the wording of this by using Silverstripes language
     * files
     *
     * @param bool|null $include_tax Should this include tax or not?
     *
     * @return string
     */
    public function getTaxString($include_tax = null)
    {
        $string = "";
        $rate = $this->getOwner()->getTaxRate();

        if (empty($include_tax)) {
            $include_tax = $this->getOwner()->ShowPriceWithTax;
        }

        if ($rate->exists() && $include_tax) {
            $string = _t(
                self::class . ".TaxIncludes",
                "inc. {title}",
                ["title" => $rate->Title]
            );
        } elseif ($rate->exists() && !$include_tax) {
            $string = _t(
                self::class . ".TaxExcludes",
                "ex. {title}",
                ["title" => $rate->Title]
            );
        }
    
        $result = $this->getOwner()->filterExtensionResults(
            $this->getOwner()->extend("updateTaxString", $string)
        );

        if (!empty($result)) {
            return $result;
        }

        return $string;
    }

    /**
     * Return a formatted price (based on locale)
     *
     * @param bool $include_tax Should the formatted price include tax?
     *
     * @return string
     */
    public function getFormattedPrice($include_tax = false)
    {
        $currency = $this->getOwner()->Currency;
        $formatter = $this->getOwner()->getFormatter();

        if ($include_tax) {
            $amount = $this->getOwner()->RoundedPriceAndTax;
        } else {
            $amount = $this->getOwner()->RoundedNoTaxPrice;
        }

        // Without currency, format as basic localised number
        if (!$currency) {
            return $formatter->format($amount);
        }

        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Get nicely formatted currency (based on current locale)
     *
     * @return string
     */
    public function getNicePrice()
    {
        return $this->getOwner()->renderWith(__CLASS__ . "_NicePrice");
    }

    /**
     * Create a field group to hold tax info.
     * 
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['BasePrice', 'TaxCategoryID', 'TaxRateID']);

        $field = FieldGroup::create(
            $this->getOwner()->dbObject("BasePrice")->scaffoldFormField(''),
            DropdownField::create('TaxCategoryID', "", TaxCategory::get())
                ->setEmptyString(
                    _t(self::class . '.SelectTaxCategory', 'Select a Tax Category')
                ),
            ReadonlyField::create("PriceOr", "")
                ->addExtraClass("text-center")
                ->setValue(_t(self::class . '.OR', ' OR ')),
            DropdownField::create(
                'TaxRateID',
                "",
                TaxRate::get()
            )->setEmptyString(
                _t(self::class . '.SelectTaxRate', 'Select a Tax Rate')
            )
        )->setName('PriceFields')
        ->setTitle($this->getOwner()->fieldLabel('Price'));

        // If we have a content field, load these fields before
        $content = $fields->dataFieldByName('Content');
        $base_field = null;

        if (!empty($content)) {
            $base_field = 'Content';
        }

        $fields->addFieldToTab(
            'Root.Main',
            $field,
            $base_field
        );
    }
}
