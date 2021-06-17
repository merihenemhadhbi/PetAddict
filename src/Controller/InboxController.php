<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 *@Route("/api/inbox")
 */
class InboxController extends AbstractController
{

    private $messageRepo;
    private $serializer;
    private $em;

    public function __construct(MessageRepository $messageRepo, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->messageRepo = $messageRepo;
        $this->serializer = $serializer;
        $this->em = $em;
    }




    /**
     * @Route("", name="get_user_inbox" , methods = "GET")
     */
    public function getUserInbox(): Response
    {
        $user_id = $this->getuser()->getEmail();
        $allUserMessages = $this->messageRepo->findByUser($user_id);
        $inbox = [];
        $inbox['messagesByUser'] = [];
        foreach ($allUserMessages as $message) {
            $theOtherUser = $message->getFromUser() != $user_id ? $message->getFromUser() : $message->getToUser();
            if (!isset($inbox['messagesByUser'][$theOtherUser])) {
                $inbox['messagesByUser'][$theOtherUser] = [$message];
            } else {
                array_push($inbox['messagesByUser'][$theOtherUser], $message);
            }
        }
        return new Response($this->serializer->serialize($inbox, 'json'), Response::HTTP_OK);
    }


    /**
     * @Route("", name="get_user_unread_messages" , methods = "PUT")
     */
    public function getUserNewMessages(): Response
    {
        $user_id = $this->getuser()->getEmail();
        $inbox = [];
        $allUserMessages = $this->messageRepo->findUserNewMessages($user_id);
        foreach ($allUserMessages as $message) {
            $theOtherUser = $message->getFromUser();
            if ($inbox[$theOtherUser] == null) {
                $inbox[$theOtherUser] = [$message];
            } else {
                array_push($inbox[$theOtherUser], $message);
            }
        }
        return new Response($this->serializer->serialize($inbox, 'json'), Response::HTTP_OK);
    }
    /**
     * @Route("", name="send_message" , methods = "POST")
     */
    public function sendMessage(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $fromUser = $this->getuser()->getEmail();
        $message = new Message();
        $message->setBody($data['body']);
        $message->setToUser($data['toUser']);
        $message->setFromUser($fromUser);
        $this->em->persist($message);
        $this->em->flush();
        return new Response($this->serializer->serialize($message, 'json'), Response::HTTP_OK);
    }
}
