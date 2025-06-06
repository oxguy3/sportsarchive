<?php

namespace App\Command;

use App\Entity\Document;
use App\Message\ReaderifyTask;
use App\Repository\DocumentRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:readerify-all',
    description: 'Queues all un-readerified PDFs on the site to be readerified.',
    hidden: false
)]
class ReaderifyAllCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $doctrine, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var DocumentRepository */
        $repo = $this->doctrine->getRepository(Document::class);

        $documents = $repo->findNonReaderifiedPdfs();

        $documentsCount = count($documents);
        $output->writeln("Found {$documentsCount} unreaderified PDFs!");

        for ($i = 0; $i < $documentsCount; ++$i) {
            $document = $documents[$i];

            $filename = $document->getFilename();
            $output->writeln("[{$i}/{$documentsCount}] {$filename}");
            $this->bus->dispatch(new ReaderifyTask($document->getId()));
        }

        $output->writeln('All PDFs have been queued!!');

        return Command::SUCCESS;
    }
}
