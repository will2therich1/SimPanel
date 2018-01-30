<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use AppBundle\Service\SettingService;

class GameServerController extends Controller
{

    /**
     * @Route("/user/servers/g/", name="userGameServers")
     */
    public function userIndexAction(Request $request)
    {

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);


        $data = [];
        $data['user'] = $user->getUserInfo();
        $data['active'] = "gameServer";
        $data['site'] = $settingService->getSiteInformation();
        // replace this example code with whatever you need
        return $this->render('userBundle/gameServers/user.servers.html.twig' , $data);
    }

}
