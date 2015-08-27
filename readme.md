# Laravel Support

A package with a lot of traits and foundational classes to accelerate
development. Made with love by C4 Tech and Design.

[![Latest Stable Version](https://poser.pugx.org/c4tech/support/v/stable)](https://packagist.org/packages/c4tech/support)
[![Build Status](https://travis-ci.org/C4Tech/laravel-support.svg?branch=master)](https://travis-ci.org/C4Tech/laravel-support)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/C4Tech/laravel-support/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/C4Tech/laravel-support/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/C4Tech/laravel-support/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/C4Tech/laravel-support/?branch=master)

## Foundational Classes

Basic functionality we use in our applications. Normally, we use a Repository
pattern to keep the specifics of the database abstracted away as well as cache
as much data as possible. Instead of using dependency injection on our
controller constructors, we normally use a Facade connected to a singleton
instance of the Repository so that we can perform static calls on the
repository similar to the default static calls to the model (e.g.
`User::find($id)`). However, the DI method should work with a little bit of
setup in the application (mainly, creating the contract and binding resolution
in a service provider).

### Request

We use Laravel as an API backend to a JavaScript-based frontend. After many
internal discussions about camelCase vs snake_case for properties, we decided
to use both! This, coupled with our base `Model` allows automatic translation
of defacto JavaScript/JSON camelCase notation and defacto Laravel Model
snake_case notation. In other words, use camelCase in JSON and snake_case in PHP.

If you want to extend `Illuminate\Foundation\Http\FormRequest` (a good idea!),
copy the `C4tech\Support\Request` class to your app and change the `BaseRequest`
being extended to `FormRequest` along with the file's namespace.

### Controller

Our controller provides a generic response handle and a schema. Instead of
calling `Response::json()` directly in controller methods, use the `respond()`
method which takes an HTTP status code (default = 200) and an optional array
of additional headers. Data comes from the Controller's `->data` array
property. Responses are converted to JSON where possible and returned in a
schematized output.

Example:

```
  try {
    $this->data['users'] = User::all();
    $this->data['success'] = true;
  catch (SomeException $error) {
    $this->errors[] = $error->getMessage();
  }
  return $this->respond();
```

Should return something similar to the following on success:

```
{
  "success": true,
  "errors": [],
  "data": {
    "users": [
      {...},
      {...},
      ...
    ]
  }
}
```

and the following on failure:

```
{
  "success": false,
  "errors": [
    "An error message"
  ],
  "data": {}
}
```

### Repository

Our repository provides a cacheable interface to the underlying model. Getter
and Setter methods allow mutating properties automagically, including model
relations. Anything not caught by the magic getter/setter method gets pushed
back to the model, so model properties are directly accessible on repository
instances. Additionally, the `boot()` method adds model event listeners to
flush relevant caches on database changes.

The property refering to the underlying model (static `$model`) is expected
to be a reference to a config item but can be a hardcoded class name.

### Model

Our model provides the DateFilter and JsonableApiModel traits, along with
injecting the `deleted_at` property into the `guarded` property and `getDates()`.



## Traits

We like to keep things as simple as possible. We've got a few traits to
provide some nice functionality.

### DateFilter

Provides query scope builders for date properties so you can write, for
example, `$user->created_before($date)`.

### JsonableApiModel

Provides functionality to convert arrayable/jsonable properties to camelCase
automatically. Uses the `c4tech.jsonify_output` config variable.

### JsonableApiRequest

A simple trait for Request classes that automatically converts received
properties to snake_case. Uses the `c4tech.snakify_json_input` config variable.

### JsonableColumn

Provides methods to be called in a property mutator and accessor to handle
transforming data into/from JSON for the DB.



## Foundational Tests

We like tests. Unit tests for packages, integration and acceptance tests for
applications. To make writing tests less copypasta, we've developed several
traits and classes just for testing. And, we went all Inception-like and
tested our tests.

### Base

Our base test class for testing. It consumes the Reflectable trait below and
provides a tearDown() method to call `Mockery::close()`.

### Facade

Simplifies testing for facades by providing a method to ensure the correct
facade accessor is returned.

### Model

Models can do a lot of things, so we make it easy to test models. This test
class consumes the Modelable, Presentable, Relatable, and Scopeable traits
below.

### Repository

A simple base test class with a method to be called during `setUp()` to ensure
access to both a mocked instance of the repository as well as its underlying
model. Consumes the Modelable trait below.



## Testing Traits

We like our testing to be straightforward, so we've bundled most of our
testing logic into traits.

### Modelable

A simple trait for models and repositories to ensure each test method gets a
fresh mock instance of a model.

### Reflectable

A nice set of methods to wrap around PHP Reflection classes. Currently can get
or set a property value, get a method or property. Automatically makes the
property or method accessible for further usage.

### Relatable

An exhaustive set of tests to verify model relationship methods.

### Scopeable

A trait for testing model query scope statements. Currently only handles scope
statements that do simple `where()` statements.



## Installation and setup

1. Add `"c4tech/support": "2.x"` to your composer requirements and run `composer update`.
2. Add `C4tech\Support\ServiceProvider` to `config/app.php` in the 'providers' array.
3. `php artisan vendor:publish`
4. Adjust `config/c4tech.php` if you wish to disable the automatic
   camelCase<->snake_case conversion.
