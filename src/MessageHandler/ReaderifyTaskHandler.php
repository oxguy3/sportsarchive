<?php
namespace App\MessageHandler;

use App\Message\ReaderifyTask;
use App\Repository\DocumentRepository;
use App\Service\Readerifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ReaderifyTaskHandler
{
    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly Readerifier $readerifier, 
    ) {
    }

    public function __invoke(ReaderifyTask $task)
    {
        $document = $this->documentRepository->find($task->getDocumentId());
        $this->readerifier->readerify($document);
    }
}