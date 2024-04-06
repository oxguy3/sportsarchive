<?php
namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Document;
use App\Message\ReaderifyTask;
use App\Service\Readerifier;

class ReaderifyAllCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:readerify-all';

    // the command description shown when running "php bin/console list"
    protected static $defaultDescription = 'Queues all un-readerified PDFs on the site to be readerified.';

    public function __construct(private readonly Readerifier $readerifier, private readonly ManagerRegistry $doctrine, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var DocumentRepository */
        $repo = $this->doctrine->getRepository(Document::class);

        $documents = $repo->findNonReaderifiedPdfs();

        $documentsCount = count($documents);
        $output->writeln("Found ${documentsCount} unreaderified PDFs!");

        for ($i = 0; $i < $documentsCount; $i++) {
            $document = $documents[$i];

            $filename = $document->getFilename();
            $output->writeln("[${i}/${documentsCount}] ${filename}");
            $this->bus->dispatch(new ReaderifyTask($document->getId()));
        }

        $output->writeln("All PDFs have been queued!!");

        return Command::SUCCESS;
    }
}