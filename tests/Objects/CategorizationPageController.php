<?php

namespace Mak001\Categorization\Tests;

use Mak001\Categorization\Extensions\CategorizationControllerExtension;
use \PageController;
use SilverStripe\Dev\TestOnly;

/**
 * Class CategorizationPageController
 * @package Mak001\Categorization\Tests
 */
class CategorizationPageController extends PageController implements TestOnly
{

    private static $extensions = [
        CategorizationControllerExtension::class,
    ];

}
