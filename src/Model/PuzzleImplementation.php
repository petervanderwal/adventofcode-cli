<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Model;

/**
 * @internal
 */
class PuzzleImplementation
{
    public function __construct(
        public readonly int $year,
        public readonly int $day,
        public readonly int $part,
        public readonly object $puzzleObject,
        public string $methodName,
    ) {
    }
}
