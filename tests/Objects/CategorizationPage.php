<?php

namespace Mak001\Categorization\Tests;

use \Page;
use SilverStripe\Dev\TestOnly;

/**
 * Class CategorizationPage
 * @package Mak001\Categorization\Tests
 */
class CategorizationPage extends Page implements TestOnly
{
    /**
     * @var array
     */
    private static $many_many = [
        'Categories' => CategorizationObject::class,
    ];
}
