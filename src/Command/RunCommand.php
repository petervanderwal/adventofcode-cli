<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Command;

use PeterVanDerWal\AdventOfCode\Cli\Attribute\TestWithDemoInput;
use PeterVanDerWal\AdventOfCode\Cli\Exception\PuzzleInputNotFoundException;
use PeterVanDerWal\AdventOfCode\Cli\Model\PuzzleImplementation;
use PeterVanDerWal\AdventOfCode\Cli\Repository\PuzzleRepository;
use PeterVanDerWal\AdventOfCode\Cli\Service\AnswerService;
use PeterVanDerWal\AdventOfCode\Cli\Service\PuzzleInputService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand('aoc:run')]
class RunCommand extends Command
{
    public const ENVIRONMENT_VARIABLE_AUTO_SUBMIT = 'ADVENT_OF_CODE_AUTO_SUBMIT';

    public const ARGUMENT_YEAR = 'year';
    public const ARGUMENT_DAY = 'day';
    public const ARGUMENT_PART = 'part';

    public const OPTION_ALL = 'all';
    public const OPTION_SUBMIT = 'submit';

    public function __construct(
        #[Autowire('%env(defined:' . self::ENVIRONMENT_VARIABLE_AUTO_SUBMIT . ')%')]
        private readonly bool $autoSubmitDefined,
        #[Autowire('%env(bool:default::' . self::ENVIRONMENT_VARIABLE_AUTO_SUBMIT . ')%')]
        private readonly bool $autoSubmit,
        private readonly PuzzleRepository $puzzleRepository,
        private readonly PuzzleInputService $puzzleInputService,
        private readonly AnswerService $answerService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_YEAR,
                description: 'The year of the puzzle(s) you want to run. Leave blank to run the last puzzle in your set'
            )
            ->addArgument(
                self::ARGUMENT_DAY,
                description: 'The day of the puzzle(s) you want to run. Leave blank to run all puzzles in the given year'
            )
            ->addArgument(
                self::ARGUMENT_PART,
                description: 'The puzzle part you want to run (1 or 2). Leave blank to run all puzzles for the given day'
            )
            ->addOption(
                self::OPTION_ALL,
                description: 'Run all puzzles of all years in your puzzle set'
            )
            ->addOption(
                self::OPTION_SUBMIT,
                mode: InputOption::VALUE_NEGATABLE,
                description: sprintf(
                    'Automatically submit your answer to Advent of Code. Default value in your project: %s. Configure your project default by configuring the environment variable %s in .env.local.',
                    $this->autoSubmitDefined ? ($this->autoSubmit ? 'submit' : 'don\'t submit') : 'always ask',
                    self::ENVIRONMENT_VARIABLE_AUTO_SUBMIT,
                ),
                default: $this->autoSubmitDefined ? $this->autoSubmit : null,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $puzzles = $this->getPuzzles($input);
        if (empty($puzzles)) {
            $io->error('No puzzle(s) found matching your filter');
            return self::INVALID;
        }

        $success = true;
        foreach ($puzzles as $puzzle) {
            $success = $this->runPuzzle($puzzle, $input, $io) || $success;
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    private function getIntegerArgument(InputInterface $input, string $argument): ?int
    {
        $stringValue = $input->getArgument($argument);
        if ($stringValue === '' || $stringValue === null) {
            return null;
        }

        $intValue = (int)$stringValue;
        if ((string)$intValue === $stringValue) {
            return $intValue;
        }

        throw new \InvalidArgumentException(
            sprintf('Value "%s" for argument %s is not a valid number.', $stringValue, $argument)
        );
    }

    /**
     * @return PuzzleImplementation[]
     */
    private function getPuzzles(InputInterface $input): array
    {
        $year = $this->getIntegerArgument($input, self::ARGUMENT_YEAR);
        if ($year === null) {
            return $input->getOption(self::OPTION_ALL)
                ? $this->puzzleRepository->list()
                : [$this->puzzleRepository->getLastPuzzle()];
        }

        return $this->puzzleRepository->filter(
            $year,
            $this->getIntegerArgument($input, self::ARGUMENT_DAY),
            $this->getIntegerArgument($input, self::ARGUMENT_PART),
        );
    }

    private function runPuzzle(PuzzleImplementation $puzzle, InputInterface $input, SymfonyStyle $io): bool
    {
        $io->title($puzzle->getName());

        foreach ($puzzle->getTestWithDemoInputs() as $demoInput) {
            if (!$this->runPuzzleWithDemoInput($puzzle, $demoInput, $io)) {
                return false;
            }
        }

        return $this->runFullPuzzle($puzzle, $input, $io);
    }

    private function runPuzzleWithDemoInput(
        PuzzleImplementation $puzzle,
        TestWithDemoInput $demoInput,
        SymfonyStyle $io,
    ): bool {
        $io->section(sprintf('Running test "%s"', $demoInput->name));

        $actualAnswer = $puzzle->run($demoInput);
        if ($actualAnswer === $demoInput->expectedAnswer) {
            $io->writeln(sprintf('<fg=green>Test successful, got expected answer: %s</>', $this->formatAnswer($actualAnswer)));
            return true;
        }

        $io->caution(
            sprintf(
                'Test failed, expected answer was: %s but got answer: %s',
                $this->formatAnswer($demoInput->expectedAnswer),
                $this->formatAnswer($actualAnswer),
            )
        );
        return false;
    }

    private function runFullPuzzle(
        PuzzleImplementation $puzzle,
        InputInterface $input,
        SymfonyStyle $io,
    ): bool {
        $io->section(sprintf('Running full puzzle'));

        try {
            $puzzleInput = $this->puzzleInputService->getPuzzleInput($puzzle->year, $puzzle->day);
        } catch (PuzzleInputNotFoundException $exception) {
            $io->error(sprintf(
                'Puzzle input not found, please store it as "%s" manually or configure your Advent of Code session id as described in %s',
                $exception->fixturePath,
                'TODO' // TODO add md file with instructions
            ));
            return false;
        }

        $answer = $puzzle->run($puzzleInput);
        $io->block('Found answer: ' . $answer, style: 'bg=cyan', padding: true);
        $isCorrectAnswer = $this->answerService->isCorrectAnswer($puzzle->year, $puzzle->day, $puzzle->part, $answer);

        if ($isCorrectAnswer) {
            $io->success('Found the correct answer that was submitted already');
            return true;
        }

        if ($isCorrectAnswer === false) {
            $io->error('Found the answer that was previously marked as incorrect');
            return false;
        }

        if ($this->shouldSubmit($input, $io)) {
            $submitResult = $this->answerService->submitAnswer($puzzle->year, $puzzle->day, $puzzle->part, $answer);
            $io->{$submitResult->correctAnswer ? 'success' : 'error'}(
                "Advent of Code said:\n\n".
                $submitResult->adventOfCodeResponse
            );
            if ($submitResult->correctAnswer !== null) {
                return $submitResult->correctAnswer;
            }
        }

        if ($io->confirm('Do you want to mark this answer as <fg=bright-green;options=bold,underscore>correct</>?', false)) {
            $this->answerService->saveAsAnswer($puzzle->year, $puzzle->day, $puzzle->part, $answer);
            $io->success('Well done!');
            return true;
        }

        if ($io->confirm('Do you want to mark this answer as <fg=red;options=bold,underscore>incorrect</>?', false)) {
            $this->answerService->saveAsWrongAnswer($puzzle->year, $puzzle->day, $puzzle->part, $answer);
            $io->warning('Too bad, better luck next time!');
            return false;
        }

        return true; // Don't know if this was the correct answer, but just continue with the next puzzle
    }

    private function shouldSubmit(InputInterface $input, SymfonyStyle $io): bool
    {
        $option = $input->getOption(self::OPTION_SUBMIT);
        if ($option !== null) {
            return $option;
        }

        return $io->confirm('Do you want me to submit this answer to Advent of Code for you?');
    }

    private function formatAnswer(string|int $answer): string
    {
        if (is_int($answer)) {
            return (string)$answer;
        }

        return str_contains($answer, "\n")
            // Indent 4 characters
            ? "\"\n    " . str_replace("\n", "\n    ", $answer) . "\n\""
            // Just simply quote
            : "\"$answer\"";
    }
}
