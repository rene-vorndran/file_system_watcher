<?php 
namespace App\Service;

use ZipArchive;

class ZipFileExtractor
{
    public function extractTo(string $zipPath, string $extractTo): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException("Failed to open ZIP file: $zipPath");
        }

        if (!$zip->extractTo($extractTo)) {
            $zip->close();
            throw new \RuntimeException("Failed to extract ZIP to: $extractTo");
        }

        $zip->close();
    }
}
