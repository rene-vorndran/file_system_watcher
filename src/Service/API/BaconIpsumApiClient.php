<?php 
namespace App\Service\API;

use Error;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class BaconIpsumApiClient
{
    private const ENDPOINT_URL = 'https://baconipsum.com/api/?type=meat-and-filler';
    public function __construct(private HttpClientInterface $httpClient) {}

    public function getRandomString(): string
    {
        
        $response = $this->httpClient->request('GET', self::ENDPOINT_URL);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf("HTTP Error %s: %s", $statusCode, $content));
        }

        return $response->toArray()[0];
    }
}