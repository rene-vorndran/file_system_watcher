<?php 
namespace App\Service\API;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Filesystem;

class MemeApiClient
{
    private const ENDPOINT_URL = 'https://meme-api.com/gimme';

    public function __construct(
        private HttpClientInterface $httpClient,
        private Filesystem $filesystem = new Filesystem()
    ) {}

    public function downloadRandomMeme(string $targetFilePath): string
    {
        $response = $this->httpClient->request('GET', self::ENDPOINT_URL);
    
        $data = $response->toArray();

        if (!isset($data['url'])) {
            throw new \RuntimeException('No image URL found in API response');
        }

        $imageUrl = $data['url'];
        $imageContent = $this->httpClient->request('GET', $imageUrl)->getContent();

        $this->filesystem->dumpFile($targetFilePath, $imageContent);

        return $targetFilePath;
    }
}
