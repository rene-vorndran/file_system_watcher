<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Event\FileChangeEvent;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class FileChangeJPEGOptimizer implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
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

        if ($guessedMimeTypes === 'image/jpeg') {
           $this->resizeJPEG($event->getFullPath(), 300);
           $this->logger->info(sprintf('Image %s was resized to a max width of 300px', $event->getFilename()));
        }
    
    }

    private function resizeJPEG(string $file, int $newWidth): void
    {
        [$width, $height] = getimagesize($file);

        $newHeight = (int) round($newWidth * $height / $width);

        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagejpeg($dst, $file, 100);

        imagedestroy($src);
        imagedestroy($dst);
    }
}
