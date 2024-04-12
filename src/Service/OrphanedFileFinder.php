<?php

namespace App\Service;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;

class OrphanedFileFinder
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly Filesystem $documentsFilesystem
    ) {}

    /**
     * @return string[]
     */
    public function findOrphanedDocuments(): array
    {
        /** @var DocumentRepository */
        $repo = $this->doctrine->getRepository(Document::class);

        $orphans = [];
        $listing = $this->documentsFilesystem->listContents('/');

        foreach ($listing as $item) {
            $fileId = $item->path();
            if (!$repo->findByFileId($fileId)) {
                $orphans[] = $fileId;
            }
        }

        return $orphans;
    }

    /**
     * @param string[] $fileIds
     */
    public function deleteDocuments(array $fileIds): void {}
}
