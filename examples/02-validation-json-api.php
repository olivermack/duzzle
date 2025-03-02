<?php

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleOptionsKeys;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Symfony\Component\Validator\Constraints as Assert;

require_once __DIR__ . '/../vendor/autoload.php';

$logger = new Logger('console');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Info));

# Setup Duzzle instance with defaults
$duzzle = DuzzleBuilder::create([
    'base_uri' => 'https://jsonplaceholder.typicode.com/',
    'input_validation' => 'informative',
    'output_validation' => 'informative',
])
    ->withDefaultSerializer()
    ->withDefaultValidator()
    ->withLogger($logger)
    ->build();

/**
 * Define a DTO class with validation constraints
 */
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

$invalidInput = new Todo();
$invalidInput->userId = -5;
$invalidInput->title = '';
$invalidInput->completed = true;

$result = $duzzle->request('POST', '/todos', [
    DuzzleOptionsKeys::INPUT => $invalidInput,
    DuzzleOptionsKeys::OUTPUT => Todo::class
]);

var_dump($result);

/**
 * console.WARNING: Validation of input `Todo` failed with 3 violation(s) {"violations":[{"property":"userId","message":"This value should be positive.","code":"778b7ae0-84d3-481a-9dec-35fdb64b1d78","value":-5},{"property":"title","message":"This value should not be blank.","code":"c1051bb4-d103-4f74-8988-acbcafc7fdc3","value":""},{"property":"title","message":"This value is too short. It should have 1 character or more.","code":"9ff3fdc4-b214-49db-8718-39c315e33d45","value":""}]} []
 * console.WARNING: Validation of output `Todo` failed with 3 violation(s) {"violations":[{"property":"userId","message":"This value should be positive.","code":"778b7ae0-84d3-481a-9dec-35fdb64b1d78","value":-5},{"property":"title","message":"This value should not be blank.","code":"c1051bb4-d103-4f74-8988-acbcafc7fdc3","value":""},{"property":"title","message":"This value is too short. It should have 1 character or more.","code":"9ff3fdc4-b214-49db-8718-39c315e33d45","value":""}]} []
 *
 * class Todo#201 (4) {
 *   public ?int $id => int(201)
 *   public int $userId => int(-5)
 *   public string $title => string(0) ""
 *   public bool $completed => true
 * }
 */