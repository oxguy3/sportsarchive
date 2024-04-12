<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\Headshot;
use App\Repository\DocumentRepository;
use App\Repository\HeadshotRepository;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;

class OrphanedFileFinder
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly Filesystem $documentsFilesystem,
        private readonly Filesystem $headshotsFilesystem
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
     * @return string[]
     */
    public function findOrphanedHeadshots(): array
    {
        /** @var HeadshotRepository */
        $repo = $this->doctrine->getRepository(Headshot::class);

        $orphans = [];
        $listing = $this->headshotsFilesystem->listContents('/');

        foreach ($listing as $item) {
            $filename = $item->path();
            if (!$repo->findByFilename($filename)) {
                $orphans[] = $filename;
            }
        }

        return $orphans;
    }
}
