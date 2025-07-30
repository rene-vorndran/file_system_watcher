<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Event\FileChangeEvent;
use App\Service\API\RequestwatcherClient;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;


class FileChangeJSONNotifier implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RequestwatcherClient $client,
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

        if ($guessedMimeTypes === 'application/json') {
            $jsonContent = file_get_contents($event->getFullPath());
            try {
                $this->client->sendContent($jsonContent);
            }
            catch (Throwable $e) {
                $this->logger->error(sprintf('Error while sending JSON File to requestcatcher: %s', $e->getMessage()));
                return;
            }
            $this->logger->info(sprintf('JSON File %s was send to requestcatcher', $event->getFilename()));
        }
    
    }
}
