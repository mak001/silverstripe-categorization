<?php

namespace Mak001\Categorization\Tests\Extensions;

use Mak001\Categorization\Tests\CategorizationObject;
use Mak001\Categorization\Tests\CategorizationPage;
use Mak001\Categorization\Tests\CategorizationPageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ViewableData;

/**
 * Class CategorizationControllerExtensionTest
 */
class CategorizationControllerExtensionTest extends FunctionalTest
{

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        CategorizationObject::class,
        CategorizationPage::class,
    ];

    /**
     * @var array
     */
    protected static $extra_controllers = [
        CategorizationPageController::class,
    ];

    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     * @var bool
     */
    protected static $use_draft_site = true;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        // Use the simple theme in tests (for page template)
        SSViewer::set_themes([
            '$public',
            'simple',
            '$default',
        ]);
    }

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testGetCategorizationTemplates()
    {
        $page = Injector::inst()->get(CategorizationPage::class);
        /** @var CategorizationPageController $controller */
        $controller = CategorizationPageController::create($page);

        $templates = $controller->getCategorizationTemplates('Categories', 'Category');
        $this->assertEquals([
            'Mak001\Categorization\Tests\CategorizationPage_Categories_Category',
            'Page_Categories_Category',
            'Mak001\Categorization\Tests\CategorizationPage_Categories',
            'Page_Categories',
            'Mak001\Categorization\Tests\CategorizationPage',
            'Page',
        ], $templates);

        $templates = $controller->getCategorizationTemplates('Categories');
        $this->assertEquals([
            'Mak001\Categorization\Tests\CategorizationPage_Categories',
            'Page_Categories',
            'Mak001\Categorization\Tests\CategorizationPage',
            'Page',
        ], $templates);

        $templates = $controller->getCategorizationTemplates();
        $this->assertEquals([
            'Mak001\Categorization\Tests\CategorizationPage',
            'Page',
        ], $templates);
    }


    public function testDisplayCategorization()
    {
        /** @var CategorizationPage $categorizationPage */
        $categorizationPage = $this->objFromFixture(CategorizationPage::class, 'categorizationPage');
        /** @var CategorizationObject $categorizationObject */
        $categorizationObject = $this->objFromFixture(CategorizationObject::class, 'categorization');
        $categorizationPage->Categories()->add($categorizationObject);

        $controller = CategorizationPageController::create($categorizationPage);

        // test invalid relation
        $page = $this->get($controller->Link('NotARelation'));
        $this->assertEquals(404, $page->getStatusCode());

        // test valid relation
        $page = $this->get($controller->Link('Categories'));
        $this->assertEquals(200, $page->getStatusCode());

        // test valid relation, invalid url segment
        $page = $this->get($controller->Link('Categories/NotAnObject'));
        $this->assertEquals(404, $page->getStatusCode());

        // test valid relation, valid url segment
        $page = $this->get($controller->Link('Categories/categorization'));
        $this->assertEquals(200, $page->getStatusCode());
    }
}
