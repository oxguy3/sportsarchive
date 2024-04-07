<?php

namespace App\Service;

use App\Entity\Document;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;

class Readerifier
{
    private string $TMP_DIR = '/tmp/sportsarchive';

    public function __construct(
        private readonly Filesystem $documentsFilesystem,
        private readonly ManagerRegistry $doctrine
    ) {}

    public function readerify(Document $document): void
    {
        // TODO: verify document is a pdf

        $docPath = $document->getFilePath();

        // ensure that the temp directory exists
        if (!is_dir($this->TMP_DIR)) {
            mkdir($this->TMP_DIR);
        }

        // download PDF to local temp file
        $tmpFilename = $document->getFileId().'.pdf';
        $tmpPath = $this->TMP_DIR.'/'.$tmpFilename;
        try {
            $fileHandle = $this->documentsFilesystem->readStream($document->getFilePath());
            file_put_contents($tmpPath, $fileHandle);
        } catch (FilesystemException|UnableToReadFile $exception) {
            // TODO: handle the error
            throw $exception;
        }

        // iterate through pages
        $pageMetadata = [];
        $pageCount = $this->getPdfPages($tmpPath);
        for ($p = 1; $p <= $pageCount; ++$p) {
            $command = 'pdftocairo -png -singlefile -f '.$p.' "'.$tmpPath.'" -';
            $descriptorspec = [
                0 => ['pipe', 'r'],  // stdin is a pipe that the child will read from
                1 => ['pipe', 'w'],  // stdout is a pipe that the child will write to
                // 2 => ["file", "/tmp/error-output.txt", "a"] // stderr is a file to write to
            ];

            $process = proc_open($command, $descriptorspec, $pipes, $this->TMP_DIR);

            if (is_resource($process)) {
                // $pipes now looks like this:
                // 0 => writeable handle connected to child stdin
                // 1 => readable handle connected to child stdout

                // don't need stdin
                fclose($pipes[0]);

                // read image into memory
                $imageData = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                // It is important that you close any pipes before calling
                // proc_close in order to avoid a deadlock
                $return_value = proc_close($process);

                // TODO: make sure pdftocairo was successful

                // use GD to get image details
                $image = imagecreatefromstring($imageData);
                $imageWidth = imagesx($image);
                $imageHeight = imagesy($image);

                // generate filename for page image
                $pageName = str_pad((string) $p, 6, '0', STR_PAD_LEFT);
                $imageFilename = $docPath.'_page'.$pageName.'.png';

                $pageMetadata[] = [
                    'pg' => $pageName,
                    'w' => $imageWidth,
                    'h' => $imageHeight,
                ];

                try {
                    $this->documentsFilesystem->write($imageFilename, $imageData);
                } catch (FilesystemException|UnableToWriteFile $exception) {
                    // TODO: handle the error
                    throw $exception;
                }
            }
        }

        $pageMetadataFilename = $docPath.'_pages.json';
        $pageMetadataJson = json_encode($pageMetadata);
        try {
            $this->documentsFilesystem->write($pageMetadataFilename, $pageMetadataJson);
        } catch (FilesystemException|UnableToWriteFile $exception) {
            // TODO: handle the error
            throw $exception;
        }

        // delete temp file
        unlink($tmpPath);

        // tag the document as BookReader-friendly
        $document->setIsBookReader(true);
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($document);
        $entityManager->flush();
    }

    /**
     * Uses pdfinfo to get the page count of a PDF stored locally
     * https://stackoverflow.com/a/14644354
     */
    public function getPdfPages(string $path): int
    {
        $cmd = '/usr/bin/pdfinfo';
        exec("$cmd \"$path\"", $output);

        $pageCount = 0;
        foreach ($output as $op) {
            if (preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                $pageCount = intval($matches[1]);
                break;
            }
        }

        return $pageCount;
    }
}
