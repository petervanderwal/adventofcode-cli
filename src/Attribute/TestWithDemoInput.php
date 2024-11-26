<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class TestWithDemoInput
{
    public function __construct(
        public readonly string $input,
        public readonly int|string $expectedAnswer,
        public readonly string $name = 'demo',
    ) {
    }
}
