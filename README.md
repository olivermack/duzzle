Duzzle: An opinionated, DTO-centric Guzzle HTTP Wrapper
===

Duzzle (_[DTOs](https://en.wikipedia.org/wiki/Data_transfer_object) + [Guzzle](https://github.com/guzzle/guzzle)_) is a lightweight extension on top of Guzzle designed to seamlessly integrate DTO serialization and validation workflows into your HTTP client calls. 
It leverages the Symfony Serializer and Validator to transform your domain objects (DTOs) into request payloads, validate them before dispatch, and then deserialize responses back into strongly typed objects—enabling a clean, high-level API around Guzzle’s powerful HTTP capabilities. 

If you’re seeking a straightforward, “DTO-first” approach to RESTful interactions without manually handling JSON or validation rules, Duzzle aims to provide an easy and extensible solution.

## Installation

Install the package via composer

```shell
$ composer install olivermack/duzzle
```

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

The main difference though is that Duzzle yields variable, deserialized results instead of a PSR response.

```php
// without any further specification/configuration the client
// will automatically deserialize the resulting data as php array
$result = $duzzle->request('GET', '/todos/1');
/**
 * array(4) {
 *   'userId' => int(1)
 *   'id' => int(1)
 *   'title' => string(18) "delectus aut autem"
 *   'completed' => bool(false)
 * }
 */
```

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
]);
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
