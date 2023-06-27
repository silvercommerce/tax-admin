<?php

namespace SilverCommerce\TaxAdmin\Model;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Security\PermissionProvider;
use SilverCommerce\GeoZones\Model\Zone;
use SilverStripe\Security\Security;

/**
 * A tax rate can be added to a product and allows you to map a product
 * to a percentage of tax.
 *
 * If added to a product, the tax will then be added to the price
 * automatically.
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package catalogue
 */
class TaxRate extends DataObject implements PermissionProvider
{

    private static $table_name = 'TaxRate';

    private static $db = [
        "Title" => "Varchar",
        "Rate" => "Decimal",
        'Global' => 'Boolean'
    ];

    private static $has_one = [
        "Site" => SiteConfig::class
    ];

    private static $many_many = [
        "Zones" => Zone::class
    ];

    private static $casting = [
        "ZonesList" => "Varchar"
    ];

    private static $summary_fields = [
        "Title",
        "Rate",
        "ZonesList"
    ];

    private static $searchable_fields = [
        "Title",
        "Rate"
    ];

    public function getZonesList()
    {
        return implode(", ", $this->Zones()->column("Name"));
    }

    public function getCMSValidator()
    {
        return RequiredFields::create([
            "Title",
            "Rate"
        ]);
    }

    public function requireDefaultRecords()
    {
        // If no tax rates, setup some defaults
        if (!TaxRate::get()->exists()) {
            $config = SiteConfig::current_site_config();
            $category = $config->TaxCategories()->first();

            $vat = TaxRate::create();
            $vat->Title = "VAT";
            $vat->Rate = 20;
            $vat->SiteID = $config->ID;
            $vat->write();
            DB::alteration_message(
                'VAT tax rate created.',
                'created'
            );
            
            $reduced = TaxRate::create();
            $reduced->Title = "Reduced rate";
            $reduced->Rate = 5;
            $reduced->SiteID = $config->ID;
            $reduced->write();
            DB::alteration_message(
                'Reduced tax rate created.',
                'created'
            );
            
            $zero = TaxRate::create();
            $zero->Title = "Zero rate";
            $zero->Rate = 0;
            $zero->SiteID = $config->ID;
            $zero->write();
            DB::alteration_message(
                'Zero tax rate created.',
                'created'
            );

            if ($category) {
                $category->Rates()->add($vat);
                DB::alteration_message(
                    'Added VAT to category',
                    'created'
                );
            }
        }
        
        parent::requireDefaultRecords();
    }

    public function providePermissions()
    {
        return [
            "TAXADMIN_MANAGE_RATE" => [
                'name' => 'Manage Tax Rates',
                'help' => 'Allow user to create, edit and delete tax rates',
                'category' => 'Tax',
                'sort' => 0
            ]
        ];
    }

    /**
     * Anyone can view tax categories
     *
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        
        if ($extended !== null) {
            return $extended;
        }

        return true;
    }

    /**
     * Anyone can create orders, even guest users
     *
     * @param Member $member
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member, $context);
        
        if ($extended !== null) {
            return $extended;
        }

        if (!$member) {
            $member = Security::getCurrentUser();
        }

        if ($member && Permission::checkMember($member->ID, ["ADMIN", "TAXADMIN_MANAGE_RATE"])) {
            return true;
        }

        return false;
    }

    /**
     * Only users with correct rights can edit
     *
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        
        if ($extended !== null) {
            return $extended;
        }

        if (!$member) {
            $member = Security::getCurrentUser();
        }

        if ($member && Permission::checkMember($member->ID, ["ADMIN", "TAXADMIN_MANAGE_RATE"])) {
            return true;
        }

        return false;
    }

    /**
     * No one should be able to delete an order once it has been created
     *
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        
        if ($extended !== null) {
            return $extended;
        }

        if (!$member) {
            $member = Security::getCurrentUser();
        }

        if ($member && Permission::checkMember($member->ID, ["ADMIN", "TAXADMIN_MANAGE_RATE"])) {
            return true;
        }

        return false;
    }
}
