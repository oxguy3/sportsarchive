<?php

namespace App\Command;

use App\Entity\Document;
use App\Service\Readerifier;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:readerify',
    description: 'Generates files to allow a PDF to work with BookReader.',
    hidden: false
)]
class ReaderifyCommand extends Command
{
    public function __construct(private readonly Readerifier $readerifier, private readonly ManagerRegistry $doctrine)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('document', InputArgument::REQUIRED, 'Document ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $documentId = $input->getArgument('document');
        if (!is_numeric($documentId)) {
            throw new \Exception('Invalid document ID');
        }
        $documentId = intval($documentId);
        if ($documentId <= 0) {
            throw new \Exception('Invalid document ID');
        }

        $document = $this->doctrine
            ->getRepository(Document::class)
            ->find($documentId);

        $this->readerifier->readerify($document);

        return Command::SUCCESS;
    }
}
