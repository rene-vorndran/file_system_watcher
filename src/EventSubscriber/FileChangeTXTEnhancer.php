<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Event\FileChangeEvent;
use App\Service\API\BaconIpsumApiClient;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;


class FileChangeTXTEnhancer implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly BaconIpsumApiClient $client,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            FileChangeEvent::CREATED => 'onFileCreated',
        ];
    }

    public function onFileCreated(FileChangeEvent $event): void
    {
        if (!file_exists( $event->getFullPath())) {
            throw new \RuntimeException(sprintf("File not found: %s", $event->getFullPath()));
        }
        $mimeTypes = new MimeTypes();
        $guessedMimeTypes = $mimeTypes->guessMimeType($event->getFullPath());

        if ($guessedMimeTypes === 'text/plain') {
            try {
                $randomString = $this->client->getRandomString();
                $this->enhanceTextFile($event->getFullPath(), $randomString);
            }
            catch (Throwable $e) {
                $this->logger->error(sprintf('Error requesting random string: %s', $e->getMessage()));
                return;
            }
            $this->logger->info(sprintf('Text File %s was enhanced by random text', $event->getFilename()));
        }
    
    }
    private function enhanceTextFile(string $filePath, string $additionalText): void 
    {
        $handle = fopen($filePath, 'a');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file for appending: $filePath");
        }

        fwrite($handle, PHP_EOL . rtrim($additionalText) . PHP_EOL);
        fclose($handle);
    }
}
