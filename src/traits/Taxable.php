<?php

namespace SilverCommerce\TaxAdmin\Traits;

use LogicException;
use NumberFormatter;

trait Taxable
{
    /**
     * Filter the results returned by an extension
     *
     * @param mixed $results Possible results
     *
     * @return mixed
     */
    public function filterTaxableExtensionResults($results)
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
     * Get currency formatter
     *
     * @return NumberFormatter
     */
    public function getFormatter()
    {
        if (!$this->hasMethod('getLocale')) {
            throw new LogicException('Object must implement \SilverCommerce\TaxAdmin\Interfaces\TaxableProvider');
        }

        return NumberFormatter::create(
            $this->getLocale(),
            NumberFormatter::CURRENCY
        );
    }

    /**
     * Get ISO 4217 currency code from curent locale
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this
            ->getFormatter()
            ->getTextAttribute(NumberFormatter::CURRENCY_CODE);
    }

    /**
     * Get a currency symbol from the current site local
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this
            ->getFormatter()
            ->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }

    /**
     * Shortcut to get the price of this product without tax
     *
     * @return float
     */
    public function getNoTaxPrice()
    {
        if (!$this->hasMethod('getBasePrice')) {
            throw new LogicException('Object must implement \SilverCommerce\TaxAdmin\Interfaces\TaxableProvider');
        }

        $price = $this->getBasePrice();

        if ($this->hasMethod('extend')) {
            $result = $this->filterTaxableExtensionResults(
                $this->extend("updateNoTaxPrice", $price)
            );

            if (!empty($result)) {
                return $result;
            }
        }

        return $price;
    }

    /**
     * Get the percentage tax rate assotiated with this field
     *
     * @return float
     */
    public function getTaxPercentage()
    {
        $percent = $this->getTaxRate()->Rate;

        if ($this->hasMethod('extend')) {
            $result = $this->filterTaxableExtensionResults(
                $this->extend("updateTaxPercentage", $percent)
            );

            if (!empty($result)) {
                return $result;
            }
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
        if (!$this->exists()) {
            return 0;
        }

        $price = $this->getBasePrice();
        $percent = $this->getTaxPercentage();

        $tax = ($price / 100) * $percent;
        $roundedTax = round($tax, 3);

        if ($this->hasMethod('extend')) {
            $result = $this->filterTaxableExtensionResults(
                $this->extend("updateTaxAmount", $tax)
            );

            if (!empty($result)) {
                return $result;
            }
        }

        return $roundedTax;
    }

    /**
     * Get the Total price and tax
     *
     * @return float
     */
    public function getPriceAndTax()
    {
        $notax = $this->getNoTaxPrice();
        $tax = $this->getTaxAmount();
        $price = round($notax + $tax, 3);

        if ($this->hasMethod('extend')) {
            $result = $this->filterTaxableExtensionResults(
                $this->extend("updatePriceAndTax", $price)
            );

            if (!empty($result)) {
                return $result;
            }
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
        if (!$this->hasMethod('getTaxRate')
            || !$this->hasMethod('getShowPriceWithTax')
        ) {
            throw new LogicException('Object must implement \SilverCommerce\TaxAdmin\Interfaces\TaxableProvider');
        }

        $string = "";
        $rate = $this->getTaxRate();

        if (empty($include_tax)) {
            $include_tax = $this->getShowPriceWithTax();
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

        if ($this->hasMethod('extend')) {
            $result = $this->filterTaxableExtensionResults(
                $this->extend("updateTaxString", $string)
            );

            if (!empty($result)) {
                return $result;
            }
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
        $currency = $this->getCurrency();
        $formatter = $this->getFormatter();

        if ($include_tax) {
            $amount = $this->getPriceAndTax();
        } else {
            $amount = $this->getNoTaxPrice();
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
        if ($this->hasMethod('renderWith')) {
            return $this->renderWith(__CLASS__ . "_NicePrice");
        }

        return "";
    }
}
