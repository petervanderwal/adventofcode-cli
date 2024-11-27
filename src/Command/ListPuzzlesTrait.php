<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Command;

use PeterVanDerWal\AdventOfCode\Cli\Model\PuzzleImplementation;
use PeterVanDerWal\AdventOfCode\Cli\Service\AnswerService;
use Symfony\Component\Console\Style\SymfonyStyle;

trait ListPuzzlesTrait
{
    protected function listPuzzles(
        SymfonyStyle $io,
        string $title,
        AnswerService $answerService,
        PuzzleImplementation ...$puzzles
    ): void {
        $table = $io->createTable()
            ->setHeaderTitle($title)
            ->setHeaders(['Year', 'Day', 'Part', 'Class', 'Method', 'Completed']);

        foreach ($puzzles as $puzzle) {
            $status = match ($answerService->getAnswerStatus($puzzle->year, $puzzle->day, $puzzle->part)) {
                true => "<fg=green>\xE2\x9C\x94</>",
                false => "<fg=red>\xE2\x9D\x8C</>",
                null => "<fg=blue;options=bold>?</>",
            };

            $table->addRow(
                [
                    $puzzle->year,
                    sprintf('%2d', $puzzle->day),
                    'part ' . $puzzle->part,
                    $puzzle->puzzleObject::class,
                    $puzzle->methodName,
                    $status,
                ]
            );
        }

        $table->render();
    }
}
