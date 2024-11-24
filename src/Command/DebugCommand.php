<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Command;

use PeterVanDerWal\AdventOfCode\Cli\Repository\PuzzleImplementationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('aoc:debug')]
class DebugCommand extends Command
{
    public function __construct(
        private readonly PuzzleImplementationRepository $puzzleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = (new Table($output))
            ->setHeaderTitle('Registered puzzles')
            ->setHeaders(['Year', 'Day', 'Part', 'Class', 'Method']);

        foreach ($this->puzzleRepository->list() as $puzzle) {
            $table->addRow(
                [
                    $puzzle->year,
                    sprintf('%2d', $puzzle->day),
                    'part ' . $puzzle->part,
                    $puzzle->puzzleObject::class,
                    $puzzle->methodName,
                ]
            );
        }

        $table->render();
        return self::SUCCESS;
    }
}
