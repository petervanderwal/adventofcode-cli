<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Model;

use PeterVanDerWal\AdventOfCode\Cli\Attribute\TestWithDemoInput;

/**
 * @internal
 */
class PuzzleImplementation
{
    private \ReflectionMethod $method;

    public function __construct(
        public readonly int $year,
        public readonly int $day,
        public readonly int $part,
        public readonly object $puzzleObject,
        public string $methodName,
    ) {
    }

    public static function getYearDayPartString(int $year, int $day, int $part): string
    {
        return sprintf('Puzzle %d.%2d (part %d)', $year, $day, $part);
    }

    public function getName(): string
    {
        return static::getYearDayPartString($this->year, $this->day, $this->part);
    }

    public function getNameAndMethod(): string
    {
        return sprintf(
            '%s method defined in %s::%s()',
            $this->getName(),
            $this->puzzleObject::class,
            $this->methodName
        );
    }

    /**
     * @return TestWithDemoInput[]
     */
    public function getTestWithDemoInputs(): array
    {
        return array_map(
            fn(\ReflectionAttribute $attribute): TestWithDemoInput => $attribute->newInstance(),
            $this->getMethod()->getAttributes(TestWithDemoInput::class)
        );
    }

    public function run(TestWithDemoInput|string $input): PuzzleResult
    {
        $puzzleInput = $input instanceof TestWithDemoInput
            ? new PuzzleInput($input->input, demoInputName: $input->name)
            : new PuzzleInput($input);

        try {
            $method = $this->getMethod();

            $startTime = hrtime(true);
            $answer = $method->invoke($this->puzzleObject, $puzzleInput);
            $endTime = hrtime(true);

            if (is_string($answer) || is_int($answer)) {
                return new PuzzleResult($answer, $endTime - $startTime);
            }
        } catch (\ReflectionException $exception) {
            throw new \BadMethodCallException(
                sprintf(
                    '%s could not be called: %s',
                    $this->getNameAndMethod(),
                    $exception->getMessage(),
                ),
                previous: $exception,
            );
        }

        throw new \UnexpectedValueException(
            sprintf(
                '%s is expected to return int|string, got %s',
                $this->getNameAndMethod(),
                get_debug_type($answer)
            )
        );
    }

    private function getMethod(): \ReflectionMethod
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->method ??= new \ReflectionMethod($this->puzzleObject, $this->methodName);
    }
}
