<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\DependencyInjection\Compiler;

use PeterVanDerWal\AdventOfCode\Cli\PeterVanDerWalAdventOfCodeCliBundle;
use PeterVanDerWal\AdventOfCode\Cli\Repository\PuzzleRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterPuzzleImplementationsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $puzzleRepositoryDefinition = $container->getDefinition(PuzzleRepository::class);

        foreach ($container->findTaggedServiceIds(PeterVanDerWalAdventOfCodeCliBundle::PUZZLE_TAG_NAME) as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $puzzleRepositoryDefinition->addMethodCall('registerPuzzle', [
                    $attributes['year'],
                    $attributes['day'],
                    $attributes['part'],
                    new Reference($serviceId),
                    $attributes['methodName'],
                ]);
            }
        }
    }
}
