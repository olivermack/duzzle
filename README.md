Duzzle: An opinionated, DTO-centric Guzzle HTTP Wrapper
===

Duzzle (_[DTOs](https://en.wikipedia.org/wiki/Data_transfer_object) + [Guzzle](https://github.com/guzzle/guzzle)_) is a lightweight extension on top of Guzzle designed to seamlessly integrate DTO serialization and validation workflows into your HTTP client calls. 
It leverages the [Symfony Serializer](https://symfony.com/doc/current/serializer.html) and (optionally) the [Symfony Validator](https://symfony.com/doc/current/validation.html) to transform
your domain objects (DTOs) into request payloads, validate them before dispatch, and then deserialize responses back into strongly typed objects—enabling a clean, high-level API around Guzzle's powerful HTTP capabilities. 

If you’re seeking a straightforward, “DTO-first” approach to RESTful interactions without manually handling JSON or validation rules, Duzzle aims to provide an easy and extensible solution.

## Installation

Install the package via composer

```shell
$ composer install olivermack/duzzle
```

## Background

Duzzle was created as a proof of concept to show how/if a single API client implementation can
reduce complexity in a domain application which deals with multiple different remote APIs.

Because [Guzzle](https://github.com/guzzle/guzzle) is a pretty well known and widely used API client in the PHP world
Duzzle was built as a "wrapper" for Guzzle right from the start - hence the weird name ;).

Duzzle works with `output` and `input` definitions for any particular API call.

In order to add an extra guard for the consumption of remote APIs, Duzzle allows the validation
of the `input` before sending it to the remote API as well as validating the `output` of
the API to ensure that the API-consumer can deal with unexpected changes or invalid data. 

## Usage

First, create an instance of `Duzzle` via the `DuzzleBuilder`:

```php
$duzzle = DuzzleBuilder::create([
    // place your guzzle default options here
    'base_uri' => 'https://jsonplaceholder.typicode.com/',
])
    ->withDefaultSerializer()
    ->build();
```

With the instance you can perform your requests. To make the usage easier Duzzle only provides
a single `request()` method, following the signature that Guzzle's `request()` provides.

To access the data which was handled by Duzzle's middlewares you need to call `getDuzzleResult()` on the response. 

```php
// without any further specification/configuration the client
// will automatically deserialize the resulting data as php array
$result = $duzzle->request('GET', '/todos/1')->getDuzzleResult();

/**
 * array(4) {
 *   'userId' => int(1)
 *   'id' => int(1)
 *   'title' => string(18) "delectus aut autem"
 *   'completed' => bool(false)
 * }
 */
```

### DTO (De)Serialization

To deserialize the response into your domain specific DTO, you need to define the class
as POPO (_plain old php object_) and tell the client to use it as `output` type:

```php
class Todo
{
    public ?int $id = null;
    public int $userId;
    public string $title;
    public bool $completed;
}

$result = $duzzle->request('GET', '/todos/1', [
    DuzzleOptionsKeys::OUTPUT => Todo::class
    // or
    'output' => Todo::class,
])->getDuzzleResult();

/**
 * class Todo#159 (4) {
 *   public ?int $id => int(1)
 *   public int $userId => int(1)
 *   public string $title => string(18) "delectus aut autem"
 *   public bool $completed => bool(false)
 * }
 */
```

To send an instance of a DTO to the API, you need to provide the instance as `input`:

```php
$newTodo = new Todo();
$newTodo->userId = 1;
$newTodo->completed = true;
$newTodo->title = 'My new task!';

$createdTodoResult = $duzzle->request('POST', '/todos', [
    DuzzleOptionsKeys::INPUT => $newTodo,
    // if we don't provide an output DTO we'll get the result as array!
    DuzzleOptionsKeys::OUTPUT => Todo::class,
]);
```

#### Custom Serializer

The default serializer setup shipped with Duzzle is configured for the most common JSON API examples.
However, if you need to use your own serializer stack you can provide it in the builder:

```php
// define your serializer
$serializer = new \Symfony\Component\Serializer\Serializer();
// pass it to the builder
$duzzle = DuzzleBuilder::create($options)
    ->withSerializer($serializer)
    ->build();
```

### Validation

To use the (optional) validation capabilities you need to install the required `symfony/validator` package.

```shell
$ composer req symfony/validator
```

Now, when you create your `Duzzle` instance, you can ask the builder to set up the default validation
behavior for you.

#### Validation Strategies

Duzzle works with different "Strategies" to determine how the validation result should affect the behavior
of the API client. 

> ℹ️ **Without specifying a strategy, no validation will actually happen** even if you 
told the builder to use the default validator!

```php
$duzzle = DuzzleBuilder::create([
    DuzzleOptionsKeys::INPUT_VALIDATION => DefaultStrategyKey::BLOCKING->value,
    // same as:
    'input_validation' => 'blocking',
    
    DuzzleOptionsKeys::OUTPUT_VALIDATION => DefaultStrategyKey::BLOCKING->value,
    // same as:
    'output_validation' => 'blocking',
    
    // ...other options
])
    ->withDefaultSerializer()
    ->withDefaultValidator()
    ->build();
```

The following strategies are shipped with Duzzle:
* `DefaultStrategyKey::NOOP` / ``noop` - does not do anything with the results but enables the validation
* `DefaultStrategyKey::INFORMATIVE` / `informative` - allows you to log validation results
* `DefaultStrategyKey::BLOCKING` / `blocking` - throws an exception when the input or output payload are considered invalid

The `informative` strategy expects a [PSR-3 logger](https://www.php-fig.org/psr/psr-3/). You need to pass one in the builder to get any
effective output using `$builder->withLogger($myLogger)`.

#### Validation constraints

With the default validator defined you can use PHP attributes to define your rules on the DTO class:

```php
use Symfony\Component\Validator\Constraints as Assert;

class Todo
{
    public ?int $id = null;
    #[Assert\Positive]
    public int $userId;
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $title;
    public bool $completed;
}
```

To validate the input, make sure your Duzzle instance is equipped with a default
`input_validation` or define it per request like so:

```php
$todo = new Todo();
$duzzle->request('POST', '/todos', [
    'input' => $todo,
    'input_validation' => 'blocking',
])->getDuzzleResult();
```

> For a working example check out `examples/02-validation-json-api.php`

## Todos

- [x] dev ecosystem 
- [x] lib builder / factories 
- [x] most crucial code quality tooling
- [ ] DTO (de)serialization
  - [x] dealing with JSON API output
  - [x] dealing with JSON API input
  - [ ] dealing with XML API output
  - [ ] dealing with XML API input
- [x] DTO validation
  - [x] validating output DTOs
  - [x] validating input DTOs
