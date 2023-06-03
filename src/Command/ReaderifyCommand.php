<?php
namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Entity\Document;
use App\Service\Readerifier;

class ReaderifyCommand extends Command
{
    private $readerifier;
    private $doctrine;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:readerify';

    // the command description shown when running "php bin/console list"
    protected static $defaultDescription = 'Generates files to allow a PDF to work with BookReader.';

    public function __construct(Readerifier $readerifier, ManagerRegistry $doctrine)
    {
        $this->readerifier = $readerifier;
        $this->doctrine = $doctrine;

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