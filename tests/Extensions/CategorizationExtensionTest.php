<?php

namespace Mak001\Categorization\Tests\Extensions;

use Mak001\Categorization\Tests\CategorizationObject;
use Mak001\Categorization\Tests\CategorizationPage;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

/**
 * Class CategorizationExtensionTest
 * @package Mak001\Categorization\Tests\Extensions
 */
class CategorizationExtensionTest extends SapphireTest
{
    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        CategorizationObject::class,
        CategorizationPage::class,
    ];

    /**
     *
     */
    public function testGetCMSFields()
    {
        /** @var CategorizationObject $object */
        $object = Injector::inst()->create(CategorizationObject::class);
        $fields = $object->getCMSFields();

        $this->assertInstanceOf(FieldList::class, $fields);
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testValidURLSegment()
    {
        /** @var CategorizationObject $object */
        $object = Injector::inst()->create(CategorizationObject::class);
        $object->URLSegment = 'valid-test';

        $this->assertTrue($object->validURLSegment());
        $object->write();
        $this->assertTrue($object->validURLSegment());

        /** @var CategorizationObject $object */
        $object2 = Injector::inst()->create(CategorizationObject::class);

        $object2->URLSegment = 'valid-test';
        $this->assertFalse($object2->validURLSegment());

        $object2->URLSegment = 'valid-test-2';
        $this->assertTrue($object2->validURLSegment());
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function testOnBeforeWrite()
    {
        /** @var CategorizationObject $object */
        $object = Injector::inst()->create(CategorizationObject::class);
        $object->URLSegment = 'write-test';
        $object->write();

        $object2 = Injector::inst()->create(CategorizationObject::class);
        $object2->URLSegment = 'write-test';
        $object2->write();

        $this->assertNotEquals($object->URLSegment, $object2->URLSegment);
        $this->assertEquals('write-test-1', $object2->URLSegment);

        $object3 = Injector::inst()->create(CategorizationObject::class);
        $object3->URLSegment = 'write-test';
        $object3->write();

        $this->assertNotEquals($object->URLSegment, $object3->URLSegment);
        $this->assertEquals('write-test-2', $object3->URLSegment);
    }
}
