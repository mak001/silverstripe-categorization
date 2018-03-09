<?php

namespace Mak001\Categorization\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\ClassInfo;
use \SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;

/**
 * Class CategorizationControllerExtension
 * @package Mak001\Categorization\Extensions
 */
class CategorizationControllerExtension extends Extension
{

    /**
     * @var array
     */
    private static $allowed_actions = [
        'displayCategorization',
    ];

    /**
     * @var array
     */
    private static $url_handlers = [
        '$Relation!/$Categorization' => 'displayCategorization',
    ];

    /**
     * @param HTTPRequest $request
     *
     * @return \SilverStripe\ORM\FieldType\DBHTMLText|string
     */
    public function displayCategorization(HTTPRequest $request)
    {
        $relationSegment = $request->param('Relation');
        $categorizationSegment = $request->param('Categorization');

        /** @var DataObject $dataRecord */
        $dataRecord = $this->owner->dataRecord;
        $relations = array_merge(
            $dataRecord::config()->get('has_one'),
            $dataRecord::config()->get('has_many'),
            $dataRecord::config()->get('many_many')
        );
        $relationSegments = $dataRecord::config()->get('relation_segments') ?: [];

        /** @var string $relationName */
        $relationName = null;
        if ($key = array_search($relationSegment, $relationSegments)) {
            $relationName = $key;
        } else if (isset($relations[$relationSegment])) {
            $relationName = $relationSegment;
        }

        /** @var DataObject $relation */
        $relation = null;
        if ($relationName !== null && isset($relations[$relationName])) {
            $relation = $relations[$relationName];
        }

        if ($relation !== null && $relation::has_extension(CategorizationExtension::class)) {
            if ($categorizationSegment) {
                $categorization = $this->owner->{$relationName}()->find('URLSegment', $categorizationSegment);
                if ($categorization) {
                    return $this->owner->customise(ArrayList::create([
                        'Categorization' => $categorization,
                    ]))->renderWith([
                        'type' => 'Layout',
                        $this->getTemplates($relationName),
                    ]);
                }

                return $this->owner->httpError(404, $categorizationSegment . ' was not found');
            }

            return $this->owner->customise(ArrayList::create([
                'Categorizations' => $this->owner->{$relationName}(),
            ]))->renderWith([
                'type' => 'Layout',
                $this->getTemplates($relationName),
            ]);
        }

        return $this->owner->httpError(404, $relationName . ' was not found');
    }

    /**
     * @param string $relationName
     * @param bool $relation
     *
     * @return array
     */
    public function getTemplates($relationName, $relation = true)
    {
        $templates = [];

        foreach ($this->getTemplate() as $value) {
            if (strpos($value, 'Extension') !== false) {
                continue;
            }

            if ($value == SiteTree::class) {
                break;
            }

            $templates[] = $relation ? $value . '_' . $relationName : $value;
        }

        return $relation ?
            array_merge($templates, $this->getTemplates($relationName, false)) :
            $templates;
    }

    /**
     * @return \Generator
     */
    public function getTemplate()
    {
        $classes = ClassInfo::ancestry($this->owner->ClassName);
        $classes[static::class] = static::class;
        $classes = array_reverse($classes);

        foreach ($classes as $key => $value) {
            yield $value;
        }
    }
}
