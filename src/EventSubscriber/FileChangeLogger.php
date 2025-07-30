<?php

namespace App\EventSubscriber;

use App\Event\FileChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class FileChangeLogger implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            FileChangeEvent::CREATED => 'onFileCreated',
            FileChangeEvent::MODIFIED => 'onFileModified',
            FileChangeEvent::DELETED => 'onFileDeleted',
        ];
    }

    public function onFileCreated(FileChangeEvent $event): void
    {
        $this->logger->info(sprintf("File %s was created", $event->getFilename()));
    }

    public function onFileModified(FileChangeEvent $event): void
    {
        $this->logger->info(sprintf("File %s was modified", $event->getFilename()));
    }

    public function onFileDeleted(FileChangeEvent $event): void
    {    
        $this->logger->info(sprintf("File %s was deleted", $event->getFilename()));
    }
}
