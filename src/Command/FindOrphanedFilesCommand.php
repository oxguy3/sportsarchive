<?php

namespace App\Command;

use App\Service\OrphanedFileFinder;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:find-orphans',
    description: 'Looks for files not linked to any Doctrine entities',
    hidden: false
)]
class FindOrphanedFilesCommand extends Command
{
    public function __construct(
        private readonly OrphanedFileFinder $finder,
        private readonly Filesystem $documentsFilesystem,
        private readonly Filesystem $headshotsFilesystem
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'one of: documents, headshots');
        $this->addOption(
            'delete',
            null,
            InputOption::VALUE_OPTIONAL,
            'Delete all unlinked files?',
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        if ($type == 'documents') {
            $orphans = $this->finder->findOrphanedDocuments();
            $output->writeln($orphans);

            if ($input->getOption('delete') !== false) {
                foreach ($orphans as $fileId) {
                    try {
                        $this->documentsFilesystem->deleteDirectory($fileId.'/');
                        $output->writeln("<info>Deleted {$fileId}</info>");
                    } catch (\Exception $exception) {
                        $message = $exception->getMessage();
                        $output->writeln("<error>Failed to delete {$fileId}: {$message}</error>");
                    }
                }
            }
        } elseif ($type == 'headshots') {
            $orphans = $this->finder->findOrphanedHeadshots();
            $output->writeln($orphans);

            if ($input->getOption('delete') !== false) {
                foreach ($orphans as $filename) {
                    try {
                        $this->headshotsFilesystem->delete($filename);
                        $output->writeln("<info>Deleted {$filename}</info>");
                    } catch (\Exception $exception) {
                        $message = $exception->getMessage();
                        $output->writeln("<error>Failed to delete {$filename}: {$message}</error>");
                    }
                }
            }
        } else {
            throw new \Exception('Unsupported type');
        }

        return Command::SUCCESS;
    }
}
