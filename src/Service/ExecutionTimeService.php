<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Service;

class ExecutionTimeService
{
    public function __construct(
        public readonly FixtureService $fixtureService,
    ) {
    }

    public function getExecutionTime(int $year, int $day, int $part): ?int
    {
        $executionTime = $this->fixtureService->getFixture($this->getExecutionTimeFixturePath($year, $day, $part));
        return $executionTime === null ? null : (int)$executionTime;
    }

    public function getExecutionTimeFormatted(int $year, int $day, int $part): ?string
    {
        $executionTime = $this->getExecutionTime($year, $day, $part);
        return $executionTime === null ? null : $this->formatTime($executionTime);
    }

    public function saveExecutionTime(
        int $year,
        int $day,
        int $part,
        int $executionTime,
        bool $onlyIfBetter,
    ): void {
        if ($onlyIfBetter) {
            $currentValue = $this->getExecutionTime($year, $day, $part);
            if ($currentValue !== null && $currentValue < $executionTime) {
                return;
            }
        }

        $this->fixtureService->storeFixture(
            $this->getExecutionTimeFixturePath($year, $day, $part),
            (string)$executionTime
        );
    }

    public function formatTime(int $executionTime): string
    {
        return match (true) {
            $executionTime < 1000           => $executionTime . ' ns',
            $executionTime < 10000          => sprintf('%.2f μs', $executionTime / 1000),
            $executionTime < 100000         => sprintf('%.1f μs', $executionTime / 1000),
            $executionTime < 1000000        => sprintf('%.0f μs', $executionTime / 1000),
            $executionTime < 10000000       => sprintf('%.2f ms', $executionTime / 1000000),
            $executionTime < 100000000      => sprintf('%.1f ms', $executionTime / 1000000),
            $executionTime < 1000000000     => sprintf('%.0f ms', $executionTime / 1000000),
            $executionTime < 10000000000    => sprintf('%.2f sec', $executionTime / 1000000000),
            $executionTime < 100000000000   => sprintf('%.1f sec', $executionTime / 1000000000),
            default                         => $this->formatSeconds((int)round($executionTime / 1000000000)),
        };
    }

    private function formatSeconds(int $seconds): string
    {
        return match (true) {
            $seconds < 60   => sprintf('%d sec', $seconds),
            $seconds < 3600 => sprintf('%d:%02d min', $seconds / 60, $seconds % 60),
            default         => sprintf('%d:%02d hour', $seconds / 3600, ($seconds / 60) % 60),
        };
    }

    private function getExecutionTimeFixturePath(int $year, int $day, int $part): string
    {
        return sprintf('%d/%d/execution-time-%d.txt', $year, $day, $part);
    }
}
