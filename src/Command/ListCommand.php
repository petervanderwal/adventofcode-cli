<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Command;

use PeterVanDerWal\AdventOfCode\Cli\Repository\PuzzleRepository;
use PeterVanDerWal\AdventOfCode\Cli\Service\AnswerService;
use PeterVanDerWal\AdventOfCode\Cli\Service\ExecutionTimeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('aoc:list', description: 'Lists all puzzle implementations within this project.')]
class ListCommand extends Command
{
    use ListPuzzlesTrait;

    public function __construct(
        private readonly PuzzleRepository $puzzleRepository,
        private readonly AnswerService $answerService,
        private readonly ExecutionTimeService $executionTimeService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->listPuzzles(
            new SymfonyStyle($input, $output),
            'Registered puzzles',
            $this->answerService,
            $this->executionTimeService,
            ...$this->puzzleRepository->list(),
        );
        return self::SUCCESS;
    }
}
