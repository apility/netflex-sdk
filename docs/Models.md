# Structure models

Netflex SDK implements a simple Model class inspired by Laravels Eloquent.

You can use models to:

- Fetch entries
- Make it simple to output JSON data
- Create entires
- Update entries
- Delete entries
- Search for entries
- Model pagination

This is the bare minimum needed to create a Model:

```php
<?php

class Article extends Netflex\Structure {
  // Structure ID to link the model with
  protected $directory = 10000;
}
```

The Model also support getter and setter method to simply custom formatting of the data. It also enabled "virtual" properties on the model instance.

```php
<?php

class Article extends Netflex\Structure {
  // Structure ID to link the model with
  protected $directory = 10000;

  public function getNameAttribute ($name) {
    return 'Hello ' . $name;
  }

  public function setNameAttribute ($name) {
    $this->attributes['name'] = $name;
  }
}
```

## Date fields

Just like Eloquent, you can enable automatic casting of date fields into Carbon instances.

```php
<?php

class Article extends Netflex\Structure {
  // Structure ID to link the model with
  protected $directory = 10000;
  protected $dates = ['created', 'updated'];
}
```

As default, `created` and `updated` will always be Carbon instances. To change this behavior, you will have to override `$dates`.

## Retrieving Entries

```php
<?php

$entry = Article::find(10001);
// $entry may be `NULL` if not found

$entry = Article::findOrFail(10001);
// $entry may not be `NULL`, throws en Exception if not found.

echo $entry->name;
```

To be backwards compatible with old Netflex sites, the model also implements `ArrayAccess`. It is however not recommended to use this unless you need it for legacy reasons.

```php
<?php

$entry = Article::first();

echo $entry['name'];
```

### Warning!

As default, the model will automatically typecast all values. This may create problems when usin the model instances with old code.

You can disable this behavior by setting the `$typecasting` attribute to `false`

## Returning as JSON

```php
<?php

$entry = Article::find(10001);

echo $entry; // Implicit converts the mode to JSON
```

## Create Entry

```php
<?php

$entry = new Article('Article name');
$entry->save();

// or
$entry = new Article([
  'name' => 'Article name',
  'published' => true,
  'author' => 'Author Name'
]);

$entry->save();
```

## Updating Entry

```php
<?php

$entry = Article::find(10001);

if ($entry) {
  $entry->name = 'Article name';
  $entry->save();
}

// or
if ($entry) {
  $entry->update([
    'name' => 'Article name',
    'author' => 'Author Name'
  ]);

  $entry->save();
}
```

## Delete Entry

```php
<?php

$entry = Article::find(10001);

if ($entry) {
  $entry->delete();
}
```

## Search for entries

```php
<?php

$entries = Article::where('published', true)
  ->where('author', 'Author Name')
  ->orWhere('author', 'Other Author')
  ->get();

// or
$entry = Article::where('author', 'Author Name')->first();

// or
$entry = Article::where('author', '!=', 'Author Name')->get();
// or
$entry = Article::where('author', 'like', 'Author Name')->get();
// or
$start = '2017-01-01 00:00:00'; // or a Carbon instance
$end   = '2018-01-01 00:00:00'; // or a Carbon instance
$entry = Article::whereBetween('created', $start, $end)->get();
// or
$entry = Article::whereBetween('created', $start, $end)
  ->where('author', 'Author Name')
  ->get();

// You can also extract specific fields
$entry = Article::whereBetween('created', $start, $end)->pluck('id');

// You can also limit the number of entries
$entry = Article::whereBetween('created', $start, $end)->take(10);

// And you can sort
$entry = Article::whereBetween('created', $start, $end)
  ->orderBy('name'); // or ->orderBy('name', 'desc');
```

## Pagination

```php
<?php

$per_page = 10;
$page = 0; // Først page is always 0

$entries = Article::paginate($per_page, $page);

/* Returns a object that looks like this:
  {
    page: 0,
    next_page: 1,
    total_pages: 10,
    total_items: 100,
    items_per_page: 10,
    items: [
      ... // Model instanser
    ]
  }
*/
```

## Lifecycle Hooks

Structures now support several kinds of lifecycle hooks. The syntax is intentionally similar to Eloquents Events. But only the most basic way of setting lifecycle hooks are supported.

The Lifecycle hooks currently supported are

 * `retrieved` - When backend is queried for an object this function is run on the object.
 * `creating` - When an object is being created. This runs before data is commited.
 * `created` - After object has been successfully created.
 * `updating` - Before an object is being updated
 * `updated` - After an object has been successfully created.
 * `saving` - Before an object is being created or updated.
 * `saved ` - After an object has been sucessfully created or saved.

### Instantiating lifecylce hooks in your service.

You can create any of the above mentioned webhooks(or more than one of the same type of hook) by creating a `protected static function boot()` in your model.
This function can then contain as many webhooks you want, similar to this.

```php
protected static function boot() {

  // add on created event
  static::created(function() {
    // Echo id to screen
    echo $this->id;
  });
}
```

### Using Pre-Event hooks for data validation.
The intended way to do pre-event validation hooks is through exceptions. Your Lifecycle hook should throw any exception when you want to abort the current operation.
You have to catch your own exceptions, the lifecycle hooks does not interfere or try to catch any thrown exceptions.
