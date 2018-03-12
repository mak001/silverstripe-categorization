# SilverStripe Categorization

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
Allows a page to show category relations. This will pick up `has_many` and `many_many` relations if the relation class has the `CategorizationExtension` applied.

```yml
CategoryPageController:
  extensions:
    - Mak001\Categorization\Extensions\CategorizationControllerExtension
```

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
When visiting `example.com/page/Categories` the templates that could be used are
```php
[
    "NameSpace/CategoryPage_Categories",
    "Page_Categories",
    "NameSpace/CategoryPage",
    "Page",
]
``` 
It will always look for `ClassName_Relation`, never for the `relation_segment` for the relation.
These should be located in the `Layouts` folder.
 
When visiting a relation link the template will be passed a `Categorizations` variable that will contain the categories in the relation.

When visiting `example.com/page/Categories/category` the templates will be the same as above.
A `Categorization` variable will be added that will contain the categorization with the given URLSegment in the specified relation.
