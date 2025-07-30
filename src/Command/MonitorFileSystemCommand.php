<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Event\FileChangeEvent;
use Psr\Log\LoggerInterface;

class MonitorFileSystemCommand extends Command
{
    protected static $defaultName = 'app:monitor-directory';
    protected static $defaultDescription = 'Monitors a given directory for file system changes';

    public function __construct(private string $monitoredDirectory, private EventDispatcherInterface $dispatcher, private LoggerInterface $logger){
        parent::__construct(self::$defaultName);
        parent::setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf ("Watching directory %s ...", $this->monitoredDirectory));
        $this->logger->info(sprintf('Directory watcher for %s was started', $this->monitoredDirectory));

        if (!is_dir($this->monitoredDirectory)){
            $output->writeln(sprintf('Directory %s does not exist! Aborting...', $this->monitoredDirectory));
            return Command::FAILURE;
        }

        $lastState = $this->scanCurrentDirectoryState();
        while (true) {
           $currentState = $this->scanCurrentDirectoryState();


            foreach ($currentState as $file => $mtime) {
                if (!isset($lastState[$file])) {
                    $output->writeln(sprintf("File: %s was created at %s", $file, date('H:i:s', $mtime)));
                    $this->dispatcher->dispatch(
                new FileChangeEvent(
                            $file, 
                            $this->monitoredDirectory . DIRECTORY_SEPARATOR . $file, 
                            $mtime
                        ), 
                        FileChangeEvent::CREATED);
                } elseif ($lastState[$file] !== $mtime) {
                    $output->writeln(sprintf("File: %s was changed at %s", $file, date('H:i:s', $mtime)));
                    $this->dispatcher->dispatch(
                new FileChangeEvent(
                            $file, 
                            $this->monitoredDirectory . DIRECTORY_SEPARATOR . $file, 
                            $mtime
                        ), 
                        FileChangeEvent::MODIFIED);
                }
            }

            foreach ($lastState as $file => $mtime) {
                if (!isset($currentState[$file])) {
                    $output->writeln(sprintf( 
                        "File: %s was deleted (last changed before at %s)", 
                        $file, 
                        date('H:i:s', $mtime)
                    ));
                    $this->dispatcher->dispatch(
                new FileChangeEvent(
                            $file, 
                            $this->monitoredDirectory . DIRECTORY_SEPARATOR . $file, 
                            $mtime
                        ), 
                        FileChangeEvent::DELETED);
                }
            }

            $lastState = $currentState;

            sleep(2); // some little sleep to not overload the system
        }

        return Command::SUCCESS;
    }

    private function scanCurrentDirectoryState(): array 
    {
        $currentState = [];
        foreach (scandir($this->monitoredDirectory) as $file) 
        {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $path = $this->monitoredDirectory . DIRECTORY_SEPARATOR . $file;
            //Currently only handling files directly in the directory not in subfolders
            if (is_file($path)) {
                $currentState[$file] = filemtime($path);
            }
        }

        return $currentState;
    }
}