<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Exception;

class PuzzleInputNotFoundException extends \RuntimeException
{
    public function __construct(
        public readonly string $fixturePath,
    ) {
        parent::__construct('Full puzzle input not found, please store it in ' . $this->fixturePath);
    }
}
