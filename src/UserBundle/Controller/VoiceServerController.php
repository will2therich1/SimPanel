<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use AppBundle\Service\SettingService;

class VoiceServerController extends Controller
{

    /**
     * @Route("/user/servers/v/", name="userVoiceServers")
     */
    public function userIndexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $user = $this->getUser();

        $data = [];
        $data['user'] = $user->getUserInfo();
        $data['active'] = "VoiceServer";
        $data['site'] = $settingService->getSiteInformation();
        // replace this example code with whatever you need
        return $this->render('userBundle/gameServers/user.voice.servers.html.twig' , $data);
    }


}
