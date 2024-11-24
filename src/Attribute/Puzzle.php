<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Puzzle
{
    public const FIRST_PUZZLE_YEAR = 2015;

    public function __construct(
        public readonly int $year,
        public readonly int $day,
        public readonly int $part,
    ) {
        if ($this->year < self::FIRST_PUZZLE_YEAR || $this->year >= date('Y')) {
            throw new \InvalidArgumentException('Year should be >= ' . self::FIRST_PUZZLE_YEAR . ' and <= ' . date('Y'));
        }
        if ($this->day < 1 || $this->day > 25) {
            throw new \InvalidArgumentException('Day should be >= 1 and <= 25');
        }
        if ($this->part < 1 || $this->part > 2) {
            throw new \InvalidArgumentException('Part should be 1 or 2');
        }
    }
}
