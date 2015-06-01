# Laravel Support

A package with a lot of traits and foundational classes to accelerate
development. Made with love by C4 Tech and Design.


## Foundational Classes

Basic functionality we use in our applications. Normally, we use Presenters to
keep views as logicless as possible. We also use a Repository pattern to keep
the specifics of the database abstracted away as well as cache as much data as
possible. Instead of using dependency injection on our controller
constructors, we normally use a Facade connected to a singleton instance of
the Repository so that we can perform static calls on the repository similar
to the default static calls to the model (e.g. `User::find($id)`).

### Controller

Our controller provides generic response handles. Instead of calling `Response`
or `View`, the `respond()` method takes a view identifier, http status code,
and an array of additional headers. Data comes from the Controller's `->data`
array property. Responses default to JSON if the frontend accepts it and will
first use `->data['json']` if it is filled before falling back to `->data`
itself. This allows the same controller to do both HTML and JSON responses.

### Model

Our model provides the DateFilter and Presentable traits, along with injected
the deleted_at property into guarded and getDates.

### Presenter

Our presenter provides access to the repository for the model so that there
can be communication between presenter and repo in a more direct fashion. Just
set the static `$repository` property on the presenter and access via the
presentable `$this->repo` accessor. Note that the `$repository` property is
expected to be a reference to a config item.

### Repository

Our repository provides a cacheable interface to the underlying model. Getter
and Setter methods allow mutating properties automagically, including model
relations. Anything not caught by the magic getter/setter method gets pushed
back to the model, so model properties are directly accessible on repository
instances. Additionally, the `boot()` method adds model event listeners to
flush relevant caches on database changes.

The property refering to the underlying model (static `$model`) is expected
to be a reference to a config item. For backwards compatibility, defaults to
that property value.



## Model Traits

We like to keep things as simple as possible. We've got a few model traits to
provide some nice functionality.

### DateFilter

Provides query scope builders for date properties so you can write, for
example, `$user->created_before($date)`.

### JsonableColumn

Provides methods to be called in a property mutator and accessor to handle
transforming data into/from JSON for the DB.

### Presentable

Provides functionality to implement a presentable model without needing to
define `getPresenter()` every time. Note that the `$presenter` static property
is expected to be a reference to a config item.



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

### Presentable

Another simple trait to test the getPresenter method for a model.

### Reflectable

A nice set of methods to wrap around PHP Reflection classes. Currently can get
or set a property value, get a method or property. Automatically makes the
property or method accessible for further usage.

### Relatable

An exhaustive set of tests to verify model relationship methods.

### Scopeable

A trait for testing model queyr scope statements. Currently only handles scope
statements that do simple `where()` statements.



## Installation and setup

1. Add `"c4tech/support": "1.x"` to your composer requirements and run `composer update`.
2. Add `C4tech\Support\ServiceProvider` to `config/app.php` in the 'providers' array.
3. `php artisan vendor:publish`
