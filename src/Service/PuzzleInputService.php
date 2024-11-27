<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Service;

use PeterVanDerWal\AdventOfCode\Cli\Exception\AdventOfCodeNotAuthorizedException;
use PeterVanDerWal\AdventOfCode\Cli\Exception\PuzzleInputNotFoundException;
use PeterVanDerWal\AdventOfCode\Cli\Exception\PuzzleInputNotYetAvailableException;

/**
 * @internal
 */
class PuzzleInputService
{
    public function __construct(
        private readonly FixtureService $fixtureService,
        private readonly AdventOfCodeHttpService $httpService,
    ) {
    }

    /**
     * @throws AdventOfCodeNotAuthorizedException
     * @throws PuzzleInputNotYetAvailableException
     * @throws PuzzleInputNotFoundException
     */
    public function getPuzzleInput(int $year, int $day): string
    {
        $fixturePath = sprintf('%d/%d/input.txt', $year, $day);
        $input = $this->fixtureService->getFixture($fixturePath);
        if ($input !== null) {
            return $input;
        }

        if ($this->httpService->isAuthenticated()) {
            $input = $this->httpService->getPuzzleInput($year, $day);
            $this->fixtureService->storeFixture($fixturePath, $input);
            return $input;
        }

        throw new PuzzleInputNotFoundException($this->fixtureService->getFullFilename($fixturePath));
    }
}
