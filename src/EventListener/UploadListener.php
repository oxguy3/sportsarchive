<?php
namespace App\EventListener;

// use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;

class UploadListener
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function onUpload(PostPersistEvent $event)
    {
        $request = $event->getRequest();
        $teamSlug = $request->get('teamSlug');

        //if everything went fine
        $response = $event->getResponse();
        // $response['success'] = true;
        return $response;
    }
}
