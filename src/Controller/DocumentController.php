<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Flysystem\Filesystem;
use App\Entity\Team;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Form\DeleteType;

class DocumentController extends AbstractController
{
    /**
     * @Route("/teams/{slug}/new-document", name="document_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createDocument(Request $request, string $slug, Filesystem $documentsFilesystem): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $document = new Document();
        $document->setTeam($team);
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->getData();

            /** @var UploadedFile $documentFile */
            $documentFile = $form->get('file')->getData();

            // this condition is needed because the 'image' field is not required
            // so the file must be processed only when a file is uploaded
            if ($documentFile) {
                $newFileId = uniqid();
                $newFilename = $this->getCleanFilename($documentFile);

                // upload the file with flysystem
                try {
                    $stream = fopen($documentFile->getRealPath(), 'r+');
                    $documentsFilesystem->writeStream($newFileId.'/'.$newFilename, $stream);
                    fclose($stream);
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    // TODO handle the error
                    throw $exception;
                }

                $document->setFileId($newFileId);
                $document->setFilename($newFilename);
            }

            // persist document to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('document/documentNew.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/documents/{id}/edit", name="document_edit", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function editDocument(Request $request, int $id, Filesystem $documentsFilesystem): Response
    {
        $document = $this->getDoctrine()
            ->getRepository(Document::class)
            ->find($id);

        if (!$document) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }

        $team = $document->getTeam();

        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->getData();

            /** @var UploadedFile $documentFile */
            $documentFile = $form->get('file')->getData();

            // this condition is needed because the 'image' field is not required
            // so the file must be processed only when a file is uploaded
            if ($documentFile) {
                $newFileId = uniqid();
                $newFilename = $this->getCleanFilename($documentFile);

                // upload the file with flysystem
                try {
                    $stream = fopen($documentFile->getRealPath(), 'r+');
                    $documentsFilesystem->writeStream($newFileId.'/'.$newFilename, $stream);
                    fclose($stream);
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    // TODO handle the error
                    throw $exception;
                }

                $document->setFileId($newFileId);
                $document->setFilename($newFilename);
            }

            // persist document to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('document/documentEdit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/documents/{id}/delete", name="document_delete", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteHeadshot(Request $request, int $id, Filesystem $documentsFilesystem): Response
    {
        $document = $this->getDoctrine()
            ->getRepository(Document::class)
            ->find($id);

        if (!$document) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }

        $team = $document->getTeam();

        $form = $this->createForm(DeleteType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $success = $documentsFilesystem->delete($document->getFilePath());
            // TODO show an error message if it fails

            // remove document from db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($document);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('document/documentDelete.html.twig', [
            'document' => $document,
            'documentUrlInfix' => $_ENV['S3_DOCUMENTS_BUCKET'].'/'.$_ENV['S3_DOCUMENTS_PREFIX'],
            'form' => $form->createView(),
        ]);
    }

    function getCleanFilename(UploadedFile $documentFile): string
    {
        // get base filename (without extension)
        $filename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);

        // remove any unsafe characters, per https://stackoverflow.com/a/2021729
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);

        // remove any runs of periods
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);

        // add file extension back on
        $filename = $filename.'.'.$documentFile->guessExtension();

        return $filename;
    }
}
