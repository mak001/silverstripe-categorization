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
        $dataRecord = $this->owner->data();
        $relations = array_merge(
            $dataRecord::config()->get('has_many'),
            $dataRecord::config()->get('many_many'),
            $dataRecord::config()->get('belongs_many_many'),
            $dataRecord::config()->get('external_relation') ?: []
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

        $viewer = new SSViewer($this->owner->getViewerTemplates());
        return $this->renderRelation($viewer, $relation, $relationName, $categorizationSegment);
    }

    /**
     * @param SSViewer $viewer
     * @param DataObject $relation
     * @param string $relationName
     */
    public function renderRelation($viewer, $relation, $relationName, $categorizationSegment)
    {
        if ($relation !== null && $relation::has_extension(CategorizationExtension::class)) {
            if ($categorizationSegment) {
                return $this->renderCategorization($viewer, $relationName, $categorizationSegment);
            }

            $this->setTemplates($viewer, $relationName);
            $dataName = $this->owner->config()->get('use_alternative_variables') ? $relationName : 'Categorizations';
            return $viewer->process($this->owner->customise(
                ArrayData::create([
                    $dataName => $this->owner->{$relationName}(),
                    'RelationName' => $relationName,
                ])
            ));
        }

        return $this->owner->httpError(404, $relationName . ' was not found');
    }

    /**
     * @param SSViewer $viewer
     * @param string $relationName
     * @param string $categorizationSegment
     */
    public function renderCategorization($viewer, $relationName, $categorizationSegment)
    {
        /** @var DataObject $categorization*/
        $categorization = $this->owner->{$relationName}()->find('URLSegment', $categorizationSegment);

        if ($categorization) {
            $singularName = $categorization->config()->get('singular_name');
            $this->setTemplates($viewer, $relationName, $singularName);
            $dataName = $this->owner->config()->get('use_alternative_variables') ? $singularName : 'Categorization';
            return $viewer->process($this->owner->customise(
                ArrayData::create([
                    $dataName => $categorization,
                    'RelationName' => $relationName,
                ])
            ));
        }

        return $this->owner->httpError(404, $categorizationSegment . ' was not found');
    }

    /**
     * sets up the viewer object (otherwise it renders the layout as the full page)
     *
     * @param SSViewer $viewer
     * @param string|bool $relationName
     * @param string|bool $singleName
     */
    public function setTemplates($viewer, $relationName = false, $singleName = false)
    {
        $templates = $this->getCategorizationTemplates($relationName, $singleName);
        $templates['type'] = 'Layout';
        $viewer->setTemplateFile('Layout', ThemeResourceLoader::inst()->findTemplate(
            $templates,
            SSViewer::get_themes()
        ));
    }

    /**
     * @param string|bool $relationName
     * @param stirng|bool $singleName
     * @return array
     */
    public function getCategorizationTemplates($relationName = false, $singleName = false)
    {
        $templates = [];

        foreach ($this->getCategorizationTemplate() as $value) {
            if (strpos($value, 'Extension') !== false) {
                continue;
            }

            if ($value == SiteTree::class) {
                break;
            }

            if ($relationName) {
                if ($singleName) {
                    $templates[] = "{$value}_{$relationName}_{$singleName}";
                } else {
                    $templates[] = "{$value}_{$relationName}";
                }
            } else {
                $templates[] = $value;
            }
        }

        if ($relationName) {
            if ($singleName) {
                return array_merge($templates, $this->getCategorizationTemplates($relationName));
            }
            return array_merge($templates, $this->getCategorizationTemplates(false));
        }
        return $templates;
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
