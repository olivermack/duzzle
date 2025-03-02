<?php

declare(strict_types=1);

namespace Duzzle\Tests\Fixtures;

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
