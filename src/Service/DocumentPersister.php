<?php

namespace App\Service;

use App\Entity\Document;
use App\Message\ReaderifyTask;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handles document editing/uploading
 */
class DocumentPersister
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly Filesystem $documentsFilesystem,
        private readonly MessageBusInterface $bus,
    ) {}

    public function persist(Document $document, ?UploadedFile $documentFile): void
    {
        // this condition is needed because the 'document' field is not required
        // so the file must be processed only when a file is uploaded
        if ($documentFile) {
            $newFileId = uniqid();
            $newFilename = $this->getCleanFilename($documentFile);

            // upload the file with flysystem
            try {
                $stream = fopen($documentFile->getRealPath(), 'r+');
                $this->documentsFilesystem->writeStream($newFileId.'/'.$newFilename, $stream);
                fclose($stream);
            } catch (\Exception $exception) {
                // TODO handle the error
                throw $exception;
            }

            $document->setFileId($newFileId);
            $document->setFilename($newFilename);
            $document->setIsBookReader(false);
        }

        // persist document to db
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($document);
        $entityManager->flush();

        // queue generation of BookReader assets
        // needs to happen after persisting to db, because document id needs to be set
        if ($documentFile) {
            $this->bus->dispatch(new ReaderifyTask($document->getId()));
        }
    }

    public function getCleanFilename(UploadedFile $documentFile): string
    {
        // get base filename (without extension)
        $filename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);

        // remove any unsafe characters, per https://stackoverflow.com/a/2021729
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);

        // remove any runs of periods
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);

        // add file extension back on, but in lowercase
        $filename = $filename.'.'.mb_strtolower(pathinfo($documentFile->getClientOriginalName(), PATHINFO_EXTENSION));

        return $filename;
    }
}
