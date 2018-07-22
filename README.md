# SilverStripe Categorization
[![Build Status](https://travis-ci.org/mak001/silverstripe-categorization.svg?branch=master)](https://travis-ci.org/mak001/silverstripe-categorization)
[![codecov](https://codecov.io/gh/mak001/silverstripe-categorization/branch/master/graph/badge.svg)](https://codecov.io/gh/mak001/silverstripe-categorization)

Easily add categorization to Pages or DataObjects and allow categories to be accessed by a nice url.

Take a page on `example.com` with the url segment `page` with a relation named `Categories`.

Categories can be accessed by visiting `example.com/page/Categories`.

Going to `example.com/page/Categories/category` will return a categorization in the `Categories` relation with the `URLSegment` of `category`.


## Requirements

- SilverStripe 4.0

## Installation

`composer require mak001/silverstripe-categorization`

## Usage

### CategorizationExtension
To create a category add `Mak001\Categorization\Extensions\CategorizationExtension` to a DataObject.
This extension will add a `Title` and `URLSegment` fields to the DataObject.

```yml
Category:
  extensions:
    - Mak001\Categorization\Extensions\CategorizationExtension
```

### CategorizationControllerExtension
Allows a page to show category relations. This will pick up `has_many`, `many_many`, and `belongs_many_many` relations if the relation class has the `CategorizationExtension` applied.

```yml
CategoryPageController:
  extensions:
    - Mak001\Categorization\Extensions\CategorizationControllerExtension
```

#### External Relations
External relations can also be made viewable.
This is useful when children of a holder page have the relations, but the holder should have the viewable relations.
This can be done with 
```php
private static $external_relation = [
    'External' => RelationClass::class,
];

public function External()
{
    return RelationClass::get();
}
```
All external relations require a method named the same as the relation name.


#### Relation url segments
Relation segments can be different than the relation name. An example of the is the `Categories` relation mapping to `categories`.
Simply add 
```php
private static $relation_segments = [
    'Categories' => 'example_segment',
];
```

Now visiting `example.com/page/example_segment` will point to the `Categories` relation, and `example.com/page/Categories` will not be found.

It will default to the relation name.

### Templating
Take a page type `NameSpace/CategoryPage` that extends `Page` with the url segment of `page`.
When visiting `example.com/page/Categories` the templates that can be used are
```php
[
    "NameSpace/CategoryPage_Categories",
    "Page_Categories",
    "NameSpace/CategoryPage",
    "Page",
]
``` 
It will always look for `ClassName_Relation`, never for the `relation_segment` for the relation.

With a categorization object with a `singular_name` of `Category` and a `url_segment` of `category`, 
when visiting `example.com/page/Categories/category` the templates that can be used are
```php
[
    "NameSpace/CategoryPage_Categories_Category",
    "Page_Categories_Category",
    "NameSpace/CategoryPage_Categories",
    "Page_Categories",
    "NameSpace/CategoryPage",
    "Page",
]
```
These should be located in the `Layouts` folder.
 
When visiting a relation link the template will be passed a `Categorizations` variable that will contain the categories in the relation.

When visiting `example.com/page/Categories/category` the templates will be the same as above.
A `Categorization` variable will be added that will contain the categorization with the given URLSegment in the specified relation.

When visiting either a relation link or a categorization object the template will be passed a `RelationName` variable that denotes what relation is currently being viewed.

#### Non generic variables
Setting `$use_alternative_variables` to true on the controller will use the relation names and the categorization `$singlur_name` in place of the generic `Categorizations` and `Categorization` variable names respectivly.

### Modifying the relation lists
Modify the lists passed to the template using the generic `modifyCategorizationList($list, $request)` extension point. THis will apply to all lists.
To have more fine tuned controll over specific relation lists use `modify{$relationName}List()` replacing `{$relationName}` with the name of the relation.
