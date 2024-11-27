<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Service;

use PeterVanDerWal\AdventOfCode\Cli\Exception\AdventOfCodeNotAuthorizedException;
use PeterVanDerWal\AdventOfCode\Cli\Model\AnswerSubmissionResult;

class AnswerService
{
    public function __construct(
        private readonly FixtureService $fixtureService,
        private readonly AdventOfCodeHttpService $httpService,
    ) {
    }

    /**
     * @return bool|null True = answer is known to be correct, false = answer is known to be incorrect, null = unknown
     *     (never submitted yet)
     */
    public function isCorrectAnswer(int $year, int $day, int $part, int|string $answer): ?bool
    {
        if ((string)$answer === $this->getSubmittedAnswer($year, $day, $part)) {
            return true;
        }
        if (in_array($answer, $this->getKnownWrongAnswers($year, $day, $part), true)) {
            return false;
        }
        return null;
    }

    public function getSubmittedAnswer(int $year, int $day, int $part): ?string
    {
        return $this->fixtureService->getFixture($this->getAnswerFixturePath($year, $day, $part));
    }

    public function saveAsAnswer(int $year, int $day, int $part, int|string $answer): void
    {
        $this->fixtureService->storeFixture($this->getAnswerFixturePath($year, $day, $part), (string)$answer);
    }

    public function getKnownWrongAnswers(int $year, int $day, int $part): array
    {
        $json = $this->fixtureService->getFixture($this->getFailureFixturePath($year, $day, $part));
        if ($json === null) {
            return [];
        }
        return json_decode($json, true);
    }

    public function saveAsWrongAnswer(int $year, int $day, int $part, int|string $answer): void
    {
        $this->fixtureService->storeFixture(
            $this->getFailureFixturePath($year, $day, $part),
            json_encode(
                [
                    ...$this->getKnownWrongAnswers($year, $day, $part),
                    $answer,
                ],
                JSON_PRETTY_PRINT,
            )
        );
    }

    /**
     * @throws AdventOfCodeNotAuthorizedException
     */
    public function submitAnswer(int $year, int $day, int $part, int|string $answer): AnswerSubmissionResult
    {
        $submissionResult = new AnswerSubmissionResult(
            $this->httpService->submitAnswer($year, $day, $part, $answer)
        );

        if ($submissionResult->correctAnswer) {
            $this->saveAsAnswer($year, $day, $part, $answer);
        } elseif ($submissionResult->correctAnswer === false) {
            $this->saveAsWrongAnswer($year, $day, $part, $answer);
        }

        return $submissionResult;
    }

    /**
     * @return bool|null True = answered correctly, false = wrong answer submitted, null = unknown
     */
    public function getAnswerStatus(int $year, int $day, int $part): ?bool
    {
        if ($this->fixtureService->fixtureExists($this->getAnswerFixturePath($year, $day, $part))) {
            return true;
        }
        if ($this->fixtureService->fixtureExists($this->getFailureFixturePath($year, $day, $part))) {
            return false;
        }
        return null;
    }

    private function getAnswerFixturePath(int $year, int $day, int $part): string
    {
        return sprintf('%d/%d/answer-%d.txt', $year, $day, $part);
    }

    private function getFailureFixturePath(int $year, int $day, int $part): string
    {
        return sprintf('%d/%d/known-wrong-answers-%d.json', $year, $day, $part);
    }
}
