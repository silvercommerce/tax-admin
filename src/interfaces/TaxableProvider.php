<?php

namespace SilverCommerce\TaxAdmin\Interfaces;

/**
 * Classes that use the Taxable trait must also implement TaxableProvider
 */
interface TaxableProvider
{
    /**
     * A base price that can be provided to Taxable calculations
     *
     * @return float
     */
    public function getBasePrice();

    /**
     * A \SilverCommerce\TaxAdmin\Model\TaxRate that can be used for
     * Taxable calculations
     * 
     * @return \SilverCommerce\TaxAdmin\Model\TaxRate
     */
    public function getTaxRate();

    /**
     * Provide a string based locale for the current object (EG 'en_GB')
     *
     * @return string
     */
    public function getLocale();

    /**
     * Should we automatically show the price including TAX?
     *
     * @return bool
     */
    public function getShowPriceWithTax();

    /**
     * Should we add a "Tax String" (EG "Includes VAT") to the rendered currency?
     *
     * @return bool|null
     */
    public function getShowTaxString();
}
