<?php

namespace App\Service;

use App\Entity\Headshot;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handles headshot editing/uploading
 */
class HeadshotPersister
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly Filesystem $headshotsFilesystem
    ) {}

    public function persist(Headshot $headshot, ?UploadedFile $imageFile): void
    {
        // this condition is needed because the 'image' field is not required
        // so the file must be processed only when a file is uploaded
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // upload the file with flysystem
            try {
                $stream = fopen($imageFile->getRealPath(), 'r+');
                $this->headshotsFilesystem->writeStream($newFilename, $stream);
                fclose($stream);
            } catch (\Exception $exception) {
                // TODO handle the error
                throw $exception;
            }

            $headshot->setFilename($newFilename);
        }

        // persist headshot to db
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($headshot);
        $entityManager->flush();
    }

    public function extractDetailsFromFilename(string $originalFilename, Headshot $headshot): Headshot
    {
        // extract person name from file name, fixing "%##" notations
        $personName = urldecode(pathinfo($originalFilename, PATHINFO_FILENAME));

        // extract jersey number from filename, if available
        $personNameJerseyMatches = [];
        if (preg_match('/^#(\d+) (.*)$/', $personName, $personNameJerseyMatches)) {
            $headshot->setJerseyNumber($personNameJerseyMatches[1]);
            $personName = $personNameJerseyMatches[2];
        }

        // extract title from filename, if available
        $personNameTitleMatches = [];
        if (preg_match('/^(.*)\|(.*)$/', $personName, $personNameTitleMatches)) {
            $headshot->setTitle($personNameTitleMatches[2]);
            $personName = $personNameTitleMatches[1];
        }

        $headshot->setPersonName($personName);

        return $headshot;
    }
}
