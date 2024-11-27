<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Command;

use Nette\Safe;
use PeterVanDerWal\AdventOfCode\Cli\Service\AdventOfCodeHttpService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand('aoc:auth')]
class AuthorizeCommand extends Command
{
    public const CONFIGURE_INSTRUCTIONS = "(re)configure your Advent of Code session id with\n    bin/console aoc:auth";

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Authorizing to Advent of Code');

        $sessionId = $this->askSessionId($io);

        $envLocalPath = $this->projectDir . '/.env.local';
        $envLocalContents = is_file($envLocalPath) ? Safe::file_get_contents($envLocalPath) : '';
        Safe::file_put_contents($envLocalPath, $this->injectSessionId($envLocalContents, $sessionId));

        $io->success('Session id stored in ' . $envLocalPath);

        return self::SUCCESS;
    }

    private function askSessionId(SymfonyStyle $io): string
    {
        $io->section('Instructions');
        $io->listing([
            'Log in to https://adventofcode.com/',
            'Open the browser Developer Tools and copy the value of the Cookie named "session"',
            'Enter the session value below',
        ]);

        $io->section('Browser Cookie instructions');
        $io->listing([
            'Google Chrome  | https://developer.chrome.com/docs/devtools/application/cookies',
            'Firefox        | https://firefox-source-docs.mozilla.org/devtools-user/storage_inspector/index.html',
            'Microsoft Edge | https://learn.microsoft.com/en-us/microsoft-edge/devtools-guide-chromium/storage/cookies',
        ]);

        return $io->ask(
            'Enter the value of the Cookie named "session" on https://adventofcode.com/',
            validator: function (?string $value) {
                if (empty($value)) {
                    throw new \InvalidArgumentException('Please enter session value');
                }

                // Strip cookie name if entered
                $value = preg_replace('/^session=/', '', $value);
                // Strip cookie metadata if entered
                $value = preg_replace('/;.*/', '', $value);

                if (preg_match('/^[0-9a-f]{28,}$/', $value)) {
                    return $value;
                }

                throw new \InvalidArgumentException(
                    'Session cookie value is expected to be a 128 character hexadecimal string'
                );
            }
        );
    }

    private function injectSessionId(string $envLocal, string $sessionId): string
    {
        $line = AdventOfCodeHttpService::ENVIRONMENT_VARIABLE_SESSION_ID . '=' . $sessionId;

        // Try to replace existing variable
        $search = '/^\s*' . AdventOfCodeHttpService::ENVIRONMENT_VARIABLE_SESSION_ID . '\s*=.*/m';
        $updatedEnvLocal = preg_replace($search, $line, $envLocal);
        if ($updatedEnvLocal !== $envLocal) {
            return $updatedEnvLocal;
        }

        // Otherwise append to the end of the file
        if (strlen($envLocal) && !str_ends_with($envLocal, "\n")) {
            $envLocal .= "\n";
        }
        $envLocal .= $line . "\n";

        return $envLocal;
    }
}
