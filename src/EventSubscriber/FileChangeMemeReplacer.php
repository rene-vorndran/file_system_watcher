<?php

namespace App\EventSubscriber;

use App\Event\FileChangeEvent;
use App\Service\API\MemeApiClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class FileChangeMemeReplacer implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MemeApiClient $memeApiClient,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            FileChangeEvent::DELETED => 'onFileDeleted',
        ];
    }


    public function onFileDeleted(FileChangeEvent $event): void
    {    
        if (file_exists( $event->getFullPath())) {
            throw new \RuntimeException(sprintf("File still exists and could not be replaces: %s", $event->getFullPath()));
        }
       
        $pathInfo = pathinfo($event->getFullPath());
        $memePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '.png';

        $this->memeApiClient->downloadRandomMeme($memePath);
        $this->logger->info(sprintf('File %s has been replaced by a meme %s', $event->getFilename(), $memePath));
    }
}
