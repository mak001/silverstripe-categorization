<?php

namespace Mak001\Categorization\Tests;

use Mak001\Categorization\Extensions\CategorizationExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class CategorizationObject
 */
class CategorizationObject extends DataObject implements TestOnly
{

    /**
     * @var array
     */
    private static $extensions = [
        CategorizationExtension::class,
    ];

    /**
     * @var array
     */
    private static $belongs_many_many = [
        'Pages' => CategorizationPage::class,
    ];

    /**
     * @param string $name
     *
     * @return string
     */
    public function fieldLabel($name)
    {
        return 'label';
    }

}
