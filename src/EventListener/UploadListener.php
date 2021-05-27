<?php
namespace App\EventListener;

// use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use App\Entity\Roster;
use App\Entity\Headshot;

class UploadListener
{
    /**
     * @var Registry
     */
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function onUpload(PostPersistEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // TODO enforce jpeg/png

        // retrieve the roster to be sure it exists
        $rosterId = $request->get('rosterId');
        $roster = $this->doctrine
            ->getRepository(Roster::class)
            ->find($rosterId);

        if (!$roster) {
            $response->setSuccess(false);
            $response->setError('No roster found for id '.$rosterId);
            // TODO: move this logic to a validator so that the file doesn't get uploaded to S3
        }

        $originalFilename = $request->files->get('file')->getClientOriginalName();
        $newFilename = $event->getFile()->getBasename();

        $headshot = new Headshot();
        $headshot->setRoster($roster);
        $headshot->setPersonName(pathinfo($originalFilename, PATHINFO_FILENAME));
        $headshot->setFilename($newFilename);

        // persist headshot to db
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($headshot);
        $entityManager->flush();

        //if everything went fine
        return $response;
    }
}
