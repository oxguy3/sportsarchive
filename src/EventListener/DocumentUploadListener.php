<?php
namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use App\Entity\Roster;
use App\Entity\Headshot;

class DocumentUploadListener
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

        throw new \Exception("blah");

        /*// TODO enforce jpeg/png

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
        $headshot->setRole($request->get('role'));
        $headshot->setFilename($newFilename);

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

        // persist headshot to db
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($headshot);
        $entityManager->flush();*/

        //if everything went fine
        return $response;
    }
}
