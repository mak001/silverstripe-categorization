<?php

namespace Mak001\Categorization\Extensions;

use SebastianBergmann\CodeCoverage\Report\Text;
use SilverStripe\CMS\Forms\SiteTreeURLSegmentField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Parsers\URLSegmentFilter;
use SilverStripe\View\Requirements;

/**
 * Class CategorizationExtension
 * @package Mak001\Categorization\Extensions
 */
class CategorizationExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'URLSegment' => 'Varchar(255)',
    ];

    /**
     * Adds an SQL index for the URLSegment
     * @var array
     */
    private static $indexes = [
        "URLSegment" => [
            'type' => 'unique',
            'columns' => [
                'URLSegment',
            ],
        ],
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        Requirements::javascript('silverstripe/cms: client/dist/js/bundle.js');
        $urlsegment = SiteTreeURLSegmentField::create("URLSegment", $this->owner->fieldLabel('URLSegment'))
            ->setDefaultURL($this->owner->getDefaultURLSegment())
            ->setURLPrefix(' ');

        $helpText = _t(
            'SiteTreeURLSegmentField.HelpChars',
            ' Special characters are automatically converted or removed.'
        );
        $urlsegment->setHelpText($helpText);

        $tab = $fields->findOrMakeTab('Root.Main');
        $tab->unshift($urlsegment);
        $tab->unshift(TextField::create('Title'));
    }

    /**
     * @return string
     */
    public function getDefaultURLSegment() {
        return 'categorization';
    }

    /**
     * @return bool|mixed
     */
    public function validURLSegment()
    {
        $filtered = DataObject::get($this->owner->getClassName())->filter([
            'URLSegment' => $this->owner->URLSegment,
        ])->exclude([
            'ID' => $this->owner->ID,
        ])->column('URLSegment');

        // If any of the extensions return `0` consider the segment invalid
        $extensionResponses = array_filter(
            (array)$this->owner->extend('augmentValidURLSegment'),
            function ($response) {
                return !is_null($response);
            }
        );
        if ($extensionResponses) {
            return min($extensionResponses);
        }

        return !$filtered;
    }

    /**
     *
     */
    public function onBeforeWrite()
    {
        // Sanitize the URLSegment field
        $filter = URLSegmentFilter::create();
        $segment = $filter->filter($this->owner->URLSegment);
        $this->owner->URLSegment = $segment;

        $count = 1;
        while (!$this->owner->validURLSegment()) {
            $this->owner->URLSegment = preg_replace('/-[0-9]+$/', null, $this->owner->URLSegment) . '-' . $count;
            $count++;
        }
    }
}
