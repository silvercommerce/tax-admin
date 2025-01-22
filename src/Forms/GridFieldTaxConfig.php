<?php

namespace SilverCommerce\TaxAdmin\Forms;

use SilverStripe\Dev\Deprecation;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;

class GridFieldTaxConfig extends GridFieldConfig
{
    public function __construct()
    {
        parent::__construct();

        $this->addComponent(GridFieldToolbarHeader::create());
        $this->addComponent(GridFieldButtonRow::create('before'));
        $this->addComponent(GridFieldAddNewButton::create('buttons-before-left'));
        $this->addComponent($sort = GridFieldSortableHeader::create());
        $this->addComponent(GridFieldDataColumns::create());
        $this->addComponent(GridField_ActionMenu::create());
        $this->addComponent(GridFieldEditButton::create());
        $this->addComponent(GridFieldDetailForm::create());

        Deprecation::withNoReplacement(function () use ($sort) {
            $sort->setThrowExceptionOnBadDataType(false);
        });

        $this->extend('updateConfig');
    }
}