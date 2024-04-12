<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Team;
use App\Form\DeleteType;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use App\Repository\TeamRepository;
use App\Service\DocumentPersister;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DocumentController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine) {}

    #[Route(path: '/documents', name: 'document_list')]
    public function listDocuments(Request $request): Response
    {
        return $this->render('document/documentList.html.twig', []);
    }

    #[Route(path: '/documents.json', name: 'document_list_json', format: 'json')]
    public function listDocumentsJson(Request $request): Response
    {
        $pageNum = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('size', 20);
        if ($pageNum <= 0 || $pageSize <= 0) {
            throw new BadRequestHttpException('Negative pagination not allowed');
        }
        if ($pageSize > 100) {
            throw new BadRequestHttpException('Page size too big');
        }

        /** @var DocumentRepository */
        $docRepo = $this->doctrine->getRepository(Document::class);
        $qb = $docRepo->createQueryBuilder('d')
            ->join('d.team', 't', 'WITH', 'd.team = t.id');

        /** @var array<array{'field': string, 'value': string}> */
        $filters = $request->query->all('filters');
        if (gettype($filters) === 'array') {
            foreach ($filters as $filter) {
                $field = $filter['field'];
                $value = $filter['value'];
                if ($field == 'team_slug') {
                    $qb->andWhere('UNACCENT(LOWER(t.name)) LIKE UNACCENT(LOWER(:team))')
                        ->setParameter('team', "%{$value}%");
                } elseif ($field == 'id') {
                    $qb->andWhere('UNACCENT(LOWER(d.title)) LIKE UNACCENT(LOWER(:title))')
                        ->setParameter('title', "%{$value}%");
                } elseif ($field == 'category' && $value != ['0' => '']) {
                    $qb->andWhere('d.category = :category')
                        ->setParameter('category', $value);
                } elseif ($field == 'language') {
                    $qb->andWhere('LOWER(d.language) = LOWER(:language)')
                        ->setParameter('language', $value);
                }
            }
        }

        $docs = (clone $qb)
            ->addOrderBy('t.name', 'ASC')
            ->addOrderBy('d.category', 'ASC')
            ->addOrderBy('d.title', 'ASC')
            ->addOrderBy('d.language', 'ASC')
            ->setFirstResult(($pageNum - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();
        $count = $qb->select('count(d.id)')
            ->getQuery()
            ->getSingleScalarResult();

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
                'language',
                'team' => [
                    'name',
                    'slug',
                ],
            ],
        ]);
        foreach ($normalDocs as &$row) {
            $row['team_name'] = $row['team']['name'];
            $row['team_slug'] = $row['team']['slug'];
            unset($row['team']);
        }
        $jsonContent = $serializer->serialize(
            [
                'last_page' => ceil($count / $pageSize),
                'data' => $normalDocs,
            ],
            'json'
        );

        return JsonResponse::fromJsonString($jsonContent);
    }

    #[Route(path: '/documents/{id}.{_format}', name: 'document_show', format: 'html', requirements: ['id' => '[\d-]+', '_format' => 'html|json'])]
    public function showDocument(Request $request, int $id, Filesystem $documentsFilesystem): Response
    {
        $document = $this->doctrine
            ->getRepository(Document::class)
            ->find($id);

        if (!$document) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }

        $fileSize = $documentsFilesystem->fileSize($document->getFilePath());

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('document/documentShow.html.twig', [
                'document' => $document,
                'fileSize' => $fileSize,
            ]);
        } elseif ($format == 'json') {
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $normalDocument = $serializer->normalize($document, null, [
                AbstractNormalizer::ATTRIBUTES => [
                    'id',
                    'fileId',
                    'filename',
                    'title',
                    'category',
                    'language',
                    'team' => [
                        'name',
                        'slug',
                    ],
                ],
            ]);
            $normalDocument['fileSize'] = $fileSize;
            $jsonContent = $serializer->serialize(
                ['document' => $normalDocument],
                'json'
            );

            return JsonResponse::fromJsonString($jsonContent);
        } else {
            throw new NotAcceptableHttpException('Unknown format: '.$format);
        }
    }

    #[Route(path: '/documents/{id}/download', name: 'document_download', requirements: ['id' => '[\d-]+'])]
    public function downloadDocument(Request $request, int $id, Filesystem $documentsFilesystem): Response
    {
        $document = $this->doctrine
            ->getRepository(Document::class)
            ->find($id);

        if (!$document) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }

        $downloadableFileStream = $documentsFilesystem->readStream($document->getFilePath());
        $mimeType = $documentsFilesystem->mimeType($document->getFilePath());
        $fileSize = $documentsFilesystem->fileSize($document->getFilePath());
        $filename = $document->getFilename();

        if (ob_get_level()) {
            ob_end_clean();
        }

        return new StreamedResponse(function () use ($downloadableFileStream) { // use ($downloadableFileStream, $mimeType, $filename) {
            fpassthru($downloadableFileStream);
        }, 200, [
            'Content-Transfer-Encoding', 'binary',
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => ('attachment; filename="'.$filename.'"'),
            'Content-Length' => $fileSize,
        ]);
    }

    #[Route(path: '/documents/{id}/pages.json', name: 'document_download_pages_metadata', requirements: ['id' => '[\d-]+'])]
    public function downloadPagesMetadata(Request $request, int $id, Filesystem $documentsFilesystem): Response
    {
        $document = $this->doctrine
            ->getRepository(Document::class)
            ->find($id);

        if (!$document) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }
        $jsonPath = $document->getFilePath().'_pages.json';

        $downloadableFileStream = $documentsFilesystem->readStream($jsonPath);
        $mimeType = $documentsFilesystem->mimeType($jsonPath);
        $fileSize = $documentsFilesystem->fileSize($jsonPath);
        $filename = $document->getFilename().'_pages.json';

        if (ob_get_level()) {
            ob_end_clean();
        }

        return new StreamedResponse(function () use ($downloadableFileStream) { // use ($downloadableFileStream, $mimeType, $filename) {
            fpassthru($downloadableFileStream);
        }, 200, [
            'Content-Transfer-Encoding', 'binary',
            'Content-Type' => 'application/json',
            'Content-Disposition' => ('attachment; filename="'.$filename.'"'),
            'Content-Length' => $fileSize,
        ]);
    }

    #[Route(path: '/teams/{slug}/new-document', name: 'document_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function createDocument(Request $request, string $slug, DocumentPersister $persister): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $team = $teamRepo->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $document = new Document();
        $document->setTeam($team);
        $form = $this->createForm(DocumentType::class, $document, [
            'is_new' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Document */
            $document = $form->getData();
            /** @var UploadedFile|null $documentFile */
            $documentFile = $form->get('file')->getData();

            $persister->persist($document, $documentFile);

            /** @var SubmitButton */
            $saveAndAddAnother = $form->get('saveAndAddAnother');
            if ($saveAndAddAnother->isClicked()) {
                return $this->redirectToRoute('document_create', [
                    'slug' => $team->getSlug(),
                ]);
            } else {
                return $this->redirectToRoute('document_show', [
                    'id' => $document->getId(),
                ]);
            }
        }

        return $this->render('document/documentNew.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/documents/{id}/edit', name: 'document_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editDocument(Request $request, int $id, DocumentPersister $persister, Filesystem $documentsFilesystem): Response
    {
        $oldDocument = $this->doctrine
            ->getRepository(Document::class)
            ->find($id);

        if (!$oldDocument) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }

        $form = $this->createForm(DocumentType::class, $oldDocument, [
            'is_new' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldFileId = $oldDocument->getFileId();
            /** @var Document */
            $newDocument = $form->getData();
            /** @var UploadedFile|null $documentFile */
            $documentFile = $form->get('file')->getData();

            $persister->persist($newDocument, $documentFile);

            // delete the old file if a new file was uploaded
            if ($documentFile) {
                try {
                    $documentsFilesystem->deleteDirectory($oldFileId.'/');
                } catch (\Exception $exception) {
                    // TODO handle the error
                    throw $exception;
                }
            }

            return $this->redirectToRoute('document_show', [
                'id' => $newDocument->getId(),
            ]);
        }

        return $this->render('document/documentEdit.html.twig', [
            'team' => $oldDocument->getTeam(),
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/documents/{id}/delete', name: 'document_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteDocument(Request $request, int $id, Filesystem $documentsFilesystem): Response
    {
        $document = $this->doctrine
            ->getRepository(Document::class)
            ->find($id);

        if (!$document) {
            throw $this->createNotFoundException('No document found for id '.$id);
        }

        $team = $document->getTeam();

        $form = $this->createForm(DeleteType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $documentsFilesystem->deleteDirectory($document->getFileId().'/');
            } catch (\Exception $exception) {
                // TODO handle the error
                throw $exception;
            }

            // remove document from db
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($document);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('document/documentDelete.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }
}
