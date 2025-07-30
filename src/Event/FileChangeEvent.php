<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FileChangeEvent extends Event
{
    public const CREATED = 'file.created';
    public const MODIFIED = 'file.modified';
    public const DELETED = 'file.deleted';

    public function __construct(
        private readonly string $filename,
        private readonly string $fullPath,
        private readonly ?int $lastModified,
    ) {}

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function getLastModified(): ?int
    {
        return $this->lastModified;
    }
}
