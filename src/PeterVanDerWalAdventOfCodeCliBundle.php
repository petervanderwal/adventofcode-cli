<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli;

use PeterVanDerWal\AdventOfCode\Cli\Attribute\Puzzle;
use PeterVanDerWal\AdventOfCode\Cli\DependencyInjection\Compiler\RegisterPuzzleImplementationsCompilerPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PeterVanDerWalAdventOfCodeCliBundle extends AbstractBundle
{
    public const PUZZLE_TAG_NAME = 'aoc.puzzle';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // Apply service tag to all methods with attribute #[Puzzle]
        $builder->registerAttributeForAutoconfiguration(
            Puzzle::class,
            static function (ChildDefinition $definition, Puzzle $attribute, \ReflectionMethod $reflectionMethod) {
                $definition->addTag(
                    self::PUZZLE_TAG_NAME,
                    [
                        'year' => $attribute->year,
                        'day' => $attribute->day,
                        'part' => $attribute->part,
                        'methodName' => $reflectionMethod->getName(),
                    ]
                );
            }
        );
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterPuzzleImplementationsCompilerPass());
    }
}
