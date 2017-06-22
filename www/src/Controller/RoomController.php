<?php
namespace App\Controller;

use App\Entity\Room;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RoomController extends Controller
{
    public function indexAction()
    {
        return $this->render('Room/index.html.twig');
    }

    public function roomAction(Room $room)
    {
        return $this->render('Room/room.html.twig', ['room' => $room]);
    }
}
