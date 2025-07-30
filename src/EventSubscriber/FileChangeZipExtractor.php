<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Event\FileChangeEvent;
use App\Service\ZipFileExtractor;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class FileChangeZipExtractor implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ZipFileExtractor $extractor,
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

        if ($guessedMimeTypes === 'application/zip') {
            $realPath = realpath($event->getFullPath());
            if ($realPath === false) {
                throw new \RuntimeException(sprintf("Cannot resolve real path for: %s", $event->getFullPath()));
            }

            $extractionPath = pathinfo($realPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($realPath, PATHINFO_FILENAME);
            $this->extractor->extractTo($event->getFullPath(),$extractionPath );
            $this->logger->info(sprintf('Zip file %s was extracted to %s', $event->getFilename(), $extractionPath));
        }
    
    }

}
