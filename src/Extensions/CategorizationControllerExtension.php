<?php

namespace Mak001\Categorization\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ThemeResourceLoader;

/**
 * Class CategorizationControllerExtension
 * @package Mak001\Categorization\Extensions
 *
 * @property \SilverStripe\CMS\Controllers\ContentController $owner
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
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function displayCategorization(HTTPRequest $request)
    {
        $relationSegment = $request->param('Relation');
        $categorizationSegment = $request->param('Categorization');

        /** @var DataObject $dataRecord */
        $dataRecord = $this->owner->dataRecord;
        $relations = array_merge(
            $dataRecord::config()->get('has_many'),
            $dataRecord::config()->get('many_many')
        );
        $relationSegments = $dataRecord::config()->get('relation_segments') ?: [];

        /** @var string $relationName */
        $relationName = null;
        if ($key = array_search($relationSegment, $relationSegments)) {
            $relationName = $key;
        } elseif (isset($relations[$relationSegment])) {
            $relationName = $relationSegment;
        }

        /** @var DataObject $relation */
        $relation = null;
        if ($relationName !== null && isset($relations[$relationName])) {
            $relation = $relations[$relationName];
        }

        if ($relation !== null && $relation::has_extension(CategorizationExtension::class)) {

            $viewer = new SSViewer($this->owner->getViewerTemplates());
            $templates = $this->getCategorizationTemplates($relationName);
            $templates['type'] = 'Layout';
            $viewer->setTemplateFile('Layout', ThemeResourceLoader::inst()->findTemplate(
                $templates,
                SSViewer::get_themes()
            ));

            if ($categorizationSegment) {
                $categorization = $this->owner->{$relationName}()->find('URLSegment', $categorizationSegment);
                if ($categorization) {
                    return $viewer->process($this->owner->customise(
                        ArrayData::create([
                            'Categorization' => $categorization,
                        ])
                    ));
                }

                return $this->owner->httpError(404, $categorizationSegment . ' was not found');
            }

            return $viewer->process($this->owner->customise(
                ArrayData::create([
                    'Categorizations' => $this->owner->{$relationName}(),
                ])
            ));
        }

        return $this->owner->httpError(404, $relationName . ' was not found');
    }

    /**
     * @param string $relationName
     * @param bool $relation
     *
     * @return array
     */
    public function getCategorizationTemplates($relationName, $relation = true)
    {
        $templates = [];

        foreach ($this->getCategorizationTemplate() as $value) {
            if (strpos($value, 'Extension') !== false) {
                continue;
            }

            if ($value == SiteTree::class) {
                break;
            }

            $templates[] = $relation ? $value . '_' . $relationName : $value;
        }

        return $relation ?
            array_merge($templates, $this->getCategorizationTemplates($relationName, false)) :
            $templates;
    }

    /**
     * @return \Generator
     */
    public function getCategorizationTemplate()
    {
        $classes = ClassInfo::ancestry($this->owner->ClassName);
        $classes[static::class] = static::class;
        $classes = array_reverse($classes);

        foreach ($classes as $key => $value) {
            yield $value;
        }
    }
}
