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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DocumentController extends AbstractController
{
    /**
     * @Route("/documents", name="document_list")
     */
    public function listDocuments(Request $request): Response
    {
        return $this->render('document/documentList.html.twig', []);
    }

    /**
     * @Route("/documents.json", name="document_list_json")
     */
    public function listDocumentsJson(Request $request): Response
    {
        $pageNum = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('size', 20);
        if ($pageSize > 100) {
            throw new BadRequestHttpException('Page size too big');
        }

        $docRepo = $this->getDoctrine()->getRepository(Document::class);
        $qb = $docRepo->createQueryBuilder('d')
            ->join('d.team', 't', 'WITH', 'd.team = t.id')
            ->addOrderBy('t.name', 'ASC')
            ->addOrderBy('d.category', 'ASC')
            ->addOrderBy('d.title', 'ASC')
            ->setFirstResult(($pageNum-1)*$pageSize)
            ->setMaxResults($pageSize);

        $filters = $request->query->get('filters', []);
        // die(print_r($filters, true));
        if (getType($filters) === 'array') {
            foreach ($filters as $filter) {
                // die(print_r($filter, true));
                $field = $filter['field'];
                $value = $filter['value'];
                if ($field == 'team_slug') {
                    $qb->andWhere('UNACCENT(LOWER(t.name)) LIKE UNACCENT(LOWER(:team))')
                        ->setParameter('team', "%${value}%");
                } else if ($field == 'id') {
                    $qb->andWhere('UNACCENT(LOWER(d.title)) LIKE UNACCENT(LOWER(:title))')
                        ->setParameter('title', "%${value}%");
                } else if ($field == 'category' && $value != ['0' => '']) {
                    $qb->andWhere('d.category = :category')
                        ->setParameter('category', $value);
                }
            }
        }

        $docs = $qb->getQuery()->getResult();
        $count = $docRepo->count([]);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $normalDocs = $serializer->normalize($docs, null, [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                'fileId',
                'filename',
                'title',
                'category',
                'team' => [
                    'name',
                    'slug',
                ],
            ]
        ]);
        foreach ($normalDocs as &$row) {
            $row['team_name'] = $row['team']['name'];
            $row['team_slug'] = $row['team']['slug'];
            unset($row['team']);
        }
        $jsonContent = $serializer->serialize(
            [
                'last_page' => ceil($count/$pageSize),
                'data' => $normalDocs,
            ],
            'json'
        );

        return JsonResponse::fromJsonString($jsonContent);
    }

    /**
     * @Route("/documents/{id}", name="document_show_by_id", requirements={"id"="[\d-]+"})
     */
    public function showDocumentById(Request $request, int $id): Response
    {
        $document = $this->getDoctrine()
            ->getRepository(Document::class)
            ->find($id);

        if (!$document) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }

        return $this->redirect("https://nyc3.digitaloceanspaces.com/".$_ENV['S3_DOCUMENTS_BUCKET'].'/'.$_ENV['S3_DOCUMENTS_PREFIX'].$document->getFilePath());
    }

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
                'type' => $team->getType(),
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
                'type' => $team->getType(),
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
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('document/documentDelete.html.twig', [
            'document' => $document,
            'documentUrlInfix' => $_ENV['S3_DOCUMENTS_BUCKET'].'/'.$_ENV['S3_DOCUMENTS_PREFIX'],
            'form' => $form->createView(),
        ]);
    }

    // TODO move this function to a more reasonable place
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
