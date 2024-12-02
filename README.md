# Advent of Code CLI Bundle

This bundle helps to create puzzle implementations for [Advent of Code](https://adventofcode.com/).

Key features:
* Tests your code against the demo input + expected answer
* Automatically downloads your full puzzle input
* Automatically submit your answer to Advent of Code

## Installation

### Prerequisites
This bundle is based on Symfony and relies on Symfony's core components Dependency Injection and Console Commands.

### Installation
Install using composer:
```bash
composer require petervanderwal/adventofcode-cli
```
And register your bundle in `config/bundles.php` if not done already by Symfony Flex:
```php
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    PeterVanDerWal\AdventOfCode\Cli\PeterVanDerWalAdventOfCodeCliBundle::class => ['all' => true],
];
```

## Features and usage

### Register puzzle solution implementation
* Create a class and method containing your puzzle solution implementation.
* Your method should accept one single argument of the type [`PuzzleInput`](src/Model/PuzzleInput.php) and should return
  an integer or string (`int|string`).
* Register your method by adding the [`#[Puzzle]`](src/Attribute/Puzzle.php) attribute. 
* Add one or more [`#[TestWithDemoInput]`](src/Attribute/TestWithDemoInput.php) attributes to test your 
  implementation against the demo input and verify it gives the expected answer.
* Make sure your puzzle class is registered as (Symfony) service.

See the [example puzzle implementation](docs/example-puzzle-implementation.md) for more guidance.

### Authenticate to Advent of Code
This bundle can retrieve your full puzzle input from Advent of Code and automatically submit your answer. Run
`bin/console aoc:auth` and follow the instructions.

### Run your puzzle solution
Use the `bin/console aoc:run` command to run your puzzle. This will first test your code against the demo input provided
with the [`#[TestWithDemoInput]`](src/Attribute/TestWithDemoInput.php) attribute(s). When these tests were successful 
it will run your code with the full puzzle input and submit your answer to Advent of Code.

#### Puzzle selection
* `bin/console aoc:run` will run the last puzzle implementation in your project (highest year + day + part).
* `bin/console aoc:run year [day] [part]` will run all puzzle implementations in the given year + optional day + optional part.
* `bin/console aoc:run --all` will run all puzzles implementations

#### Automatically submitting your solution
The automatic submission of your solution can be managed with
* Using the `--submit` or `--no-submit` flags on `bin/console aoc:run`
* Setting the `ADVENT_OF_CODE_AUTO_SUBMIT` environment value (e.g. in `.env.local`)

### List all your puzzle solutions
Run `bin/console aoc:list` to list all your puzzle implementations within your project.

### IDE integration
To run your puzzle implementation directly from your IDE, the binary `vendor/bin/puzzle-php-bridge` can be used. Read 
the [IDE configuration instructions](docs/puzzle-php-bridge.md).

## Credits
A big shout-out to [Eric Wastl](http://was.tl/) and his team for his dedication on creating [Advent of Code](https://adventofcode.com/)
for that many years in a row already! Note that Advent of Code was not involved in the development of this bundle and is 
therefore not responsible for any shortcomings you may find here.

## License
This bundle is published under the [MIT License](LICENSE.md).
