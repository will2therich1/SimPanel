<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;


class GameServerController extends Controller
{

    /**
     * @Route("/user/servers/g/", name="userGameServers")
     */
    public function userIndexAction(Request $request)
    {

        $user = $this->getUser();

        $data = [];
        $data['user'] = $user->getUserInfo();
        $data['active'] = "GameServer";
        // replace this example code with whatever you need
        return $this->render('userBundle/gameServers/user.servers.html.twig' , $data);
    }
}
