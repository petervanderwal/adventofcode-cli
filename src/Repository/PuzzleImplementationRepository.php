<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Repository;

use PeterVanDerWal\AdventOfCode\Cli\Model\PuzzleImplementation;

/**
 * @internal
 */
class PuzzleImplementationRepository
{
    /** @var array<string, array{0: object, 1: string}> */
    private array $puzzles = [];

    public function registerPuzzle(int $year, int $day, int $part, object $puzzleObject, string $methodName): void
    {
        $key = $this->getKey($year, $day, $part);
        if (!isset($this->puzzles[$key])) {
            $this->puzzles[$key] = [$puzzleObject, $methodName];
            return;
        }

        throw new \BadMethodCallException(
            sprintf(
                'The puzzle %d.%2d (part %d) is already registered at %s::%s(), can\'t register it again as %s::%s()',
                $year,
                $day,
                $part,
                $this->puzzles[$key][0]::class,
                $this->puzzles[$key][1],
                $puzzleObject::class,
                $methodName
            )
        );
    }

    public function getPuzzle(int $year, int $day, int $part): PuzzleImplementation
    {
        $key = $this->getKey($year, $day, $part);
        if (!isset($this->puzzles[$key])) {
            throw new \OutOfBoundsException(sprintf('Puzzle %d.%2d (part %d) not found', $year, $day, $part));
        }

        return new PuzzleImplementation($year, $day, $part, $this->puzzles[$key][0], $this->puzzles[$key][1]);
    }

    /**
     * @return iterable<PuzzleImplementation>
     */
    public function list(): iterable
    {
        $keys = array_keys($this->puzzles);
        sort($keys);

        foreach ($keys as $key) {
            [$year, $day, $part] = explode('.', $key);
            yield $this->getPuzzle((int)$year, (int)$day, (int)$part);
        }
    }

    private function getKey(int $year, int $day, int $part): string
    {
        return sprintf('%d.%d.%d', $year, $day, $part);
    }
}
