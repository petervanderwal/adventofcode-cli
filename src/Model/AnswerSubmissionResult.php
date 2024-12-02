<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Model;

class AnswerSubmissionResult
{
    public readonly ?bool $correctAnswer;

    public function __construct(
        public readonly string $adventOfCodeResponse,
    ) {
        if (preg_match('/(?<! not )the\s+right\s+answer/i', $this->adventOfCodeResponse)) {
            $this->correctAnswer = true;
        } elseif (
            preg_match(
                '/You\s+don.t\s+seem\s+to\s+be\s+solving\s+the\s+right\s+level/i',
                $this->adventOfCodeResponse
            )
            || preg_match(
                '/You\s+gave\s+an\s+answer\s+too\s+recently/i',
                $this->adventOfCodeResponse
            )
        ) {
            $this->correctAnswer = null;
        } else {
            $this->correctAnswer = false;
        }
    }
}
