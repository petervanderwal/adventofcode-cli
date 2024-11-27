<?php

declare(strict_types=1);

namespace PeterVanDerWal\AdventOfCode\Cli\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
class AdventOfCodeHttpService
{
    public const ENVIRONMENT_VARIABLE_BASE_URI = 'ADVENT_OF_CODE_BASE_URI';
    public const ENVIRONMENT_VARIABLE_SESSION_ID = 'ADVENT_OF_CODE_SESSION_ID';
    private const DEFAULT_BASE_URI = 'https://adventofcode.com/';

    private readonly string $baseUri;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(default::' . self::ENVIRONMENT_VARIABLE_BASE_URI . ')%')]
        ?string $baseUri,
        #[Autowire('%env(default::' . self::ENVIRONMENT_VARIABLE_SESSION_ID . ')%')]
        private readonly ?string $sessionId,
    ) {
        $this->baseUri = $baseUri ?? self::DEFAULT_BASE_URI;
    }

    public function isAuthenticated(): bool
    {
        // Simple check for now, can be extended later on to verify if the session is still valid
        return $this->sessionId !== null;
    }

    public function getPuzzleInput(int $year, int $day): string
    {
        return $this->request(
            Request::METHOD_GET,
            sprintf('%d/day/%d/input', $year, $day)
        )->getContent();
    }

    public function submitAnswer(int $year, int $day, int $part, int|string $answer): string
    {
        $response = $this->request(
            Request::METHOD_POST,
            sprintf('%d/day/%d/answer', $year, $day),
            [
                'body' => [
                    'level' => $part,
                    'answer' => $answer,
                ],
            ]
        )->getContent();

        return $this->getArticlePlainText($response);
    }

    private function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $response = $this->httpClient->request(
            $method,
            $this->baseUri . $url,
            array_replace_recursive($options, [
                'headers' => [
                    'Cookie' => 'session=' . $this->sessionId,
                ]
            ]),
        );

        // TODO verify response code and provide better error message

        return $response;
    }

    private function getArticlePlainText(string $content): string
    {
        if (preg_match('#<article[^>]*>(.*)</article[^>]*>#si', $content, $matches)) {
            $content = $matches[1];
        }
        $content = preg_replace('#</?p[^>]*>#i', "\n", $content);
        return trim(strip_tags($content));
    }
}