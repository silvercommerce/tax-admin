<?php

namespace SilverCommerce\TaxAdmin\Model;

use Locale;
use LogicException;
use SilverCommerce\GeoZones\Helpers\GeoZonesHelper;
use SilverStripe\ORM\DB;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;

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
class TaxCategory extends DataObject implements PermissionProvider
{
    
    private static $table_name = 'TaxCategory';

    /**
     * What location will this rate be applied based on?
     *
     * @var array
     * @config
     */
    private static $rate_locations = [
        'Shipping Address',
        'Billing Address',
        'Store Address'
    ];

    private static $db = [
        "Title" => "Varchar",
        "Default" => "Boolean"
    ];
    
    private static $has_one = [
        "Site" => SiteConfig::class
    ];

    private static $many_many = [
        "Rates" => TaxRate::class
    ];

    private static $many_many_extraFields = [
        "Rates" => [
            "Location" => "Int"
        ]
    ];

    private static $summary_fields = [
        "Title",
        "RatesList",
        "Default"
    ];

    private static $casting = [
        "RatesList" => "Varchar(255)"
    ];
    
    public function getRatesList()
    {
        return implode(", ", $this->Rates()->column("Title"));
    }

    /**
     * Attempt to determine the relevent current tax
     * from the users location (or default store location)
     *
     * @param string $country The current ISO-3166 2 character country code
     * @param string $region An ISO-3166-2 subdivision code
     * @return TaxRate|null
     */
    public function ValidTax($country = null, $region = null)
    {
        try {
            if (empty($country)) {
                // First try and get the locale from the member
                $member = Security::getCurrentUser();

                if (!empty($member) && $member->getLocale()) {
                    $locale = Locale::parseLocale($member->getLocale());
                }

                if (isset($locale['region'])) {
                    $country = $locale['region'];
                }
            }

            if (empty($country)) {
                $country = i18n::get_locale();
            }

            GeoZonesHelper::create([$country]);
        } catch (LogicException $e) {
            $country = Locale::getRegion($country);
        }

        $filter = [
            'Global' => 1,
            "Zones.Country:PartialMatch" => $country
        ];

        $rates = $this
            ->Rates()
            ->filterAny($filter);

        if (isset($region)) {
            $rates = $rates->addFilter([
                "Zones.RegionCodes:PartialMatch" =>
                $region
            ]);
        }

        return $rates->first();
    }
    
    public function getCMSValidator()
    {
        return RequiredFields::create([
            "Title"
        ]);
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $site = SiteConfig::current_site_config();
            $grid = $fields->dataFieldByName("Rates");

            if (!empty($grid)) {
                $fields->removeByName('Rates');
                $config = $grid->getConfig();

                $add_field = new GridFieldAddExistingAutocompleter('buttons-before-right');

                if ($add_field && $site) {
                    $dataClass = $grid->getModelClass();
                    $add_field->setSearchList(
                        DataList::create($dataClass)->filter('Site.ID', $site->ID)
                    );
                }

                $config
                    ->removeComponentsByType(GridFieldAddNewButton::class)
                    ->removeComponentsByType(GridFieldEditButton::class)
                    ->removeComponentsByType(GridFieldDeleteAction::class)
                    ->removeComponentsByType(GridFieldDetailForm::class)
                    ->removeComponentsByType(GridFieldAddExistingAutocompleter::class)
                    ->addComponent($add_field)
                    ->addComponent(new GridFieldDeleteAction(true));
                
                $fields->addFieldToTab('Root.Main', $grid);
            }
        });

        return parent::getCMSFields();
    }
    
    public function requireDefaultRecords()
    {
        // If no tax rates, setup some defaults
        if (!TaxCategory::get()->exists()) {
            $config = SiteConfig::current_site_config();

            $cat = TaxCategory::create([
                "Title" => "Standard Goods",
                "Default" => 1
            ]);
            $cat->SiteID = $config->ID;
            $cat->write();

            DB::alteration_message(
                'Standard Goods tax category created',
                'created'
            );
        }
        
        parent::requireDefaultRecords();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // If this is set to default, reset all other categories
        if ($this->Default && $this->Site()->exists()) {
            foreach ($this->Site()->TaxCategories() as $cat) {
                if ($cat->ID != $this->ID && $cat->Default) {
                    $cat->Default = false;
                    $cat->write();
                }
            }
        }
    }

    public function providePermissions()
    {
        return [
            "TAXADMIN_MANAGE_CATEGORY" => [
                'name' => 'Manage Tax Categories',
                'help' => 'Allow user to create, edit and delete tax categoires',
                'category' => 'Tax',
                'sort' => 10
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

        if ($member && Permission::checkMember($member->ID, ["ADMIN", "TAXADMIN_MANAGE_CATEGORY"])) {
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

        if ($member && Permission::checkMember($member->ID, ["ADMIN", "TAXADMIN_MANAGE_CATEGORY"])) {
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

        if ($member && Permission::checkMember($member->ID, ["ADMIN", "TAXADMIN_MANAGE_CATEGORY"])) {
            return true;
        }

        return false;
    }
}
