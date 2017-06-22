<?php
namespace App\Controller;

use App\Entity\Room;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RoomController extends Controller
{
    public function indexAction()
    {
        $rooms = $this->getDoctrine()->getManager()->getRepository('App:Room')->findBy(
            [], ['name' => 'ASC']
        );
        return $this->render('Room/index.html.twig', ['rooms' => $rooms]);
    }

    public function roomAction(Room $room)
    {
        $messages = $this->getDoctrine()->getManager()->getRepository('App:Message')->findBy(
            ['roomId' => $room->getId()], ['createdAt' => 'ASC']
        );
        return $this->render('Room/room.html.twig', ['room' => $room, 'messages' => $messages]);
    }
}
