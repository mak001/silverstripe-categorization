<?php

namespace Mak001\Categorization\Extensions;

use SilverStripe\CMS\Forms\SiteTreeURLSegmentField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

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
        'Title' => 'Varchar',
        'URLSegment' => 'Varchar',
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
        $urlsegment = SiteTreeURLSegmentField::create("URLSegment", $this->owner->fieldLabel('URLSegment'))
            ->setURLPrefix('');

        $helpText = _t('SiteTreeURLSegmentField.HelpChars',
            ' Special characters are automatically converted or removed.');
        $urlsegment->setHelpText($helpText);

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title'),
            $urlsegment,
        ]);
    }
}
