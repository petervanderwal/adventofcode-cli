# Advent of Code CLI Bundle

## Example puzzle implementation

```php

<?php

declare(strict_types=1);

namespace App\Puzzle;

use PeterVanDerWal\AdventOfCode\Cli\Attribute\Puzzle;
use PeterVanDerWal\AdventOfCode\Cli\Attribute\TestWithDemoInput;
use PeterVanDerWal\AdventOfCode\Cli\Model\PuzzleInput;

// Your classname doesn't really matter, you can be as creative as you want. You can even implement all puzzle solutions
// in one single class if that helps you. 
// Do make sure your class is registered as (Symfony) service though, otherwise the #[Puzzle] attribute won't be seen
// by this bundle.
class Day01
{
    // Register your puzzle solution by adding the #[Puzzle] attribute to the method. This attribute should be unique,
    // you can't have two methods with the same year+day+part 
    #[Puzzle(year: 2023, day: 1, part: 1)]
    
    // Every puzzle in Advent of Code contains an example input with an expected answer. It is good practice to test
    // your implementation against this example. If this test fails, this bundle will not (try to) execute & submit the
    // full puzzle. 
    #[TestWithDemoInput(
        input: <<<EOF
            1abc2
            pqr3stu8vwx
            a1b2c3d4e5f
            treb7uchet
            EOF,
        expectedAnswer: 142
    )]

    // You can provide more tests if needed. You can provide a name to be used in error messages when the test fails.
    #[TestWithDemoInput(input: 'and a 2nd test', expectedAnswer: 22, name: 'demo 2')]
    
    // Same as the classname, the name of the method itself doesn't matter
    public function part1(PuzzleInput $input): int|string
    {
        // The method should accept one single argument of the type PuzzleInput.  and contains some extra information
        // about the input (whether it is a demo input or full input) that can be helpful during debugging.
        
        // The PuzzleInput object is of the type UnicodeString provided by
        // Symfony (https://symfony.com/doc/current/string.html#usage), supporting operations such as:
        $chunks = $input->trimEnd('.')->chunk(15);
        
        // but can also be converted to a raw string if that's more useful to you:
        $rawInput = (string)$input;
        
        // Next to that it provides some extra information about the input that can be helpful during debugging:
        $isDemoInput = $input->isDemoInput();
        $demoInputName = $input->demoInputName;
        
        // Last but not least, your method should return either an integer or a string:
        $answer = $this->someCalculation();
        return $answer;
    }

    // You can add the second part of the puzzle within the same class, but you can also create a separate class for it.
    // As long as it has the #[Puzzle] attribute on it
    #[Puzzle(year: 2023, day: 1, part: 2)]
    public function part2(PuzzleInput $input): int|string
    {
        // ...
    }
}
```
