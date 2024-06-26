<?php

namespace App\Message;

class ReaderifyTask
{
    public function __construct(
        private readonly int $documentId,
    ) {}

    public function getDocumentId(): int
    {
        return $this->documentId;
    }
}
