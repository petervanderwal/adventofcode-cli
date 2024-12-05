<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Model;

class PuzzleResult
{
    public function __construct(
        public readonly int|string $answer,
        public readonly int $executionTime,
    ) {
    }
}
