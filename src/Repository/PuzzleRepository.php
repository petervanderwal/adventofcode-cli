<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Repository;

use PeterVanDerWal\AdventOfCode\Cli\Model\PuzzleImplementation;

/**
 * @internal
 */
class PuzzleRepository
{
    /** @var array<int, PuzzleImplementation> */
    private array $puzzles = [];

    public function registerPuzzle(int $year, int $day, int $part, object $puzzleObject, string $methodName): void
    {
        $key = $this->getKey($year, $day, $part);
        if (!isset($this->puzzles[$key])) {
            $this->puzzles[$key] = new PuzzleImplementation($year, $day, $part, $puzzleObject, $methodName);
            return;
        }

        throw new \BadMethodCallException(
            sprintf(
                '%s is already registered at %s::%s(), can\'t register it again as %s::%s()',
                $this->puzzles[$key]->getName(),
                $this->puzzles[$key]->puzzleObject::class,
                $this->puzzles[$key]->methodName,
                $puzzleObject::class,
                $methodName
            )
        );
    }

    public function getPuzzle(int $year, int $day, int $part): PuzzleImplementation
    {
        $key = $this->getKey($year, $day, $part);
        if (!isset($this->puzzles[$key])) {
            throw new \OutOfBoundsException(
                PuzzleImplementation::getYearDayPartString($year, $day, $part) . ' not found'
            );
        }
        return $this->puzzles[$key];
    }

    /**
     * @return PuzzleImplementation[]
     */
    public function list(): array
    {
        ksort($this->puzzles);
        return array_values($this->puzzles);
    }

    /**
     * @return PuzzleImplementation[]
     */
    public function filterYear(?int $year = null, ?int $day = null, ?int $part = null): array
    {
        return array_filter(
            $this->list(),
            fn (PuzzleImplementation $puzzle): bool =>
                ($year === null || $puzzle->year === $year)
                && ($day === null || $puzzle->day === $day)
                && ($part === null || $puzzle->part === $part)
        );
    }

    /**
     * @return PuzzleImplementation[]
     */
    public function filterPath(string $path, ?int $day = null, ?int $part = null): array
    {
        if (is_file($path)) {
            $fileCondition = fn(string $file): bool => $file === $path;
        } elseif (is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $fileCondition = fn(string $file): bool => str_starts_with($file, $path);
        } else {
            throw new \InvalidArgumentException('Path not found: ' . $path);
        }

        return array_filter(
            $this->list(),
            fn (PuzzleImplementation $puzzle): bool =>
                $fileCondition(
                    (new /* @noinspection */ \ReflectionClass($puzzle->puzzleObject))->getFileName()
                )
                && ($day === null || $puzzle->day === $day)
                && ($part === null || $puzzle->part === $part)
        );
    }

    public function getLastPuzzle(): PuzzleImplementation
    {
        if (empty($this->puzzles)) {
            throw new \OutOfBoundsException('No puzzles registered');
        }
        $key = max(array_keys($this->puzzles));
        return $this->puzzles[$key];
    }

    private function getKey(int $year, int $day, int $part): int
    {
        return $year * 1000 + $day * 10 + $part;
    }
}
