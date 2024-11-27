<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Command;

use PeterVanDerWal\AdventOfCode\Cli\Repository\PuzzleRepository;
use PeterVanDerWal\AdventOfCode\Cli\Service\AnswerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('aoc:list')]
class ListCommand extends Command
{
    use ListPuzzlesTrait;

    public function __construct(
        private readonly PuzzleRepository $puzzleRepository,
        private readonly AnswerService $answerService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->listPuzzles(
            new SymfonyStyle($input, $output),
            'Registered puzzles',
            $this->answerService,
            ...$this->puzzleRepository->list(),
        );
        return self::SUCCESS;
    }
}
