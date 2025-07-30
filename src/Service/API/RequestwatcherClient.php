<?php 
namespace App\Service\API;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RequestwatcherClient
{
    private const ENDPOINT_URL = 'https://fswatcher.requestcatcher.com/';
    public function __construct(private HttpClientInterface $httpClient) {}

    public function sendContent(string $content): void
    {
        
        $response = $this->httpClient->request('POST', self::ENDPOINT_URL, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $content,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf("HTTP Error %s: %s", $statusCode, $content));
        }

    }
}