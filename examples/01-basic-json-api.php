<?php

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleOptionsKeys;

require_once __DIR__ . '/../vendor/autoload.php';

# Setup Duzzle instance with defaults
$duzzle = DuzzleBuilder::create([
    'base_uri' => 'https://jsonplaceholder.typicode.com/',
])
    ->withDefaultSerializer()
    ->build();

// get a resource as deserialized array from the JSON api
$response = $duzzle->request('GET', '/todos/1');

var_dump($response->getDuzzleResult());
/**
 * array(4) {
 *   'userId' => int(1)
 *   'id' => int(1)
 *   'title' => string(18) "delectus aut autem"
 *   'completed' => bool(false)
 * }
 */

/**
 * Now we can use the same instance also to deserialize the API response in a DTO.
 * First, we need to define the DTO class:
 */
class Todo
{
    public ?int $id = null;
    public int $userId;
    public string $title;
    public bool $completed;
}

$response = $duzzle->request('GET', '/todos/1', [
    DuzzleOptionsKeys::OUTPUT => Todo::class
]);

var_dump($response->getDuzzleResult());
/**
 * class Todo#159 (4) {
 *   public ?int $id => int(1)
 *   public int $userId => int(1)
 *   public string $title => string(18) "delectus aut autem"
 *   public bool $completed => bool(false)
 * }
 */

/**
 * Now lets see how to send a DTO to the API
 */
$newTodo = new Todo();
$newTodo->userId = 1;
$newTodo->completed = true;
$newTodo->title = 'My new task!';

$createdTodoResponse = $duzzle->request('POST', '/todos', [
    DuzzleOptionsKeys::INPUT => $newTodo,
//    'input' => ['title' => 'foooo'],
    // if we don't provide an output DTO we'll get the result as array!
    DuzzleOptionsKeys::OUTPUT => Todo::class,
]);

var_dump($createdTodoResponse->getDuzzleResult());
/**
 * class Todo#170 (4) {
 *   public ?int $id => int(201)
 *   public int $userId => int(1)
 *   public string $title => string(12) "My new task!"
 *   public bool $completed => bool(true)
 * }
 */

