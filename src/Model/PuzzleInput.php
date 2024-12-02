<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Model;

use Symfony\Component\String\UnicodeString;

class PuzzleInput extends UnicodeString
{
    public function __construct(
        string $string = '',
        public readonly ?string $demoInputName = null,
    ) {
        parent::__construct(rtrim($string, "\n\r\0"));
    }

    public function __sleep(): array
    {
        return [...parent::__sleep(), 'demoInputName'];
    }

    public function isDemoInput(): bool
    {
        return $this->demoInputName !== null;
    }

    /**
     * Split and map the input
     *
     * @template callbackParam of ($asUnicodeString is true ? UnicodeString : string)
     * @template callbackReturn
     * @param string $delimiter
     * @param callable(callbackParam): callbackReturn|null $callback
     * @param bool $asUnicodeString
     * @return ($callback is null ? callbackParam[] : callbackReturn[])
     */
    public function splitMap(
        string $delimiter,
        callable $callback = null,
        bool $asUnicodeString = false,
    ) {
        $callback ??= fn ($line) => $line;
        if (!$asUnicodeString) {
            $callback = fn ($line) => $callback((string)$line);
        }
        return array_map(
            $callback,
            $this->split($delimiter)
        );
    }

    /**
     * @return string[]
     */
    public function splitLines(): array
    {
        return $this->splitMap("\n");
    }

    /**
     * @return int[]
     */
    public function splitInt(string $delimiter = ','): array
    {
        return $this->splitMap($delimiter, fn (string $value) => (int)$value);
    }
}
