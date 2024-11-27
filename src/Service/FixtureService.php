<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Service;

use Nette\Safe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class FixtureService
{
    public const ENVIRONMENT_VARIABLE_FIXTURE_PATH = 'ADVENT_OF_CODE_FIXTURE_PATH';
    private const DEFAULT_FIXTURE_PATH = '%kernel.project_dir%/var/advent-of-code';

    private readonly string $basePath;

    public function __construct(
        #[Autowire('%env(default::' . self::ENVIRONMENT_VARIABLE_FIXTURE_PATH . ')%')]
        ?string $basePath,
        #[Autowire('@container.env_var_processor')]
        EnvVarProcessorInterface $envVarProcessor,
    ) {
        $this->basePath = $envVarProcessor->getEnv(
            'resolve',
            $basePath ?? self::DEFAULT_FIXTURE_PATH,
            fn (string $name): string => $_ENV[$name] ?? $name,
        );
    }

    public function fixtureExists(string $path): bool
    {
        return is_file($this->getFullFilename($path));
    }

    public function getFixture(string $path): ?string
    {
        $filename = $this->getFullFilename($path);
        if (!file_exists($filename)) {
            return null;
        }
        return Safe::file_get_contents($filename);
    }

    public function storeFixture(string $path, string $contents): void
    {
        $filename = $this->getFullFilename($path);
        $this->ensureDirectoryExists(dirname($filename));
        Safe::file_put_contents($filename, $contents);
    }

    public function getFullFilename(string $path): string
    {
        $path = trim($path, ' /');

        if ($path === '') {
            throw new \InvalidArgumentException('Fixture path can\'t be empty');
        }

        if (
            str_starts_with($path, '../')
            || str_contains($path, '/../')
            || str_ends_with($path, '/.')
            || str_ends_with($path, '/..')
        ) {
            throw new \InvalidArgumentException('Path traversal is not allowed for fixtures');
        }

        return $this->basePath . '/' . $path;
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            Safe::mkdir($path, recursive: true);
        }
    }
}
