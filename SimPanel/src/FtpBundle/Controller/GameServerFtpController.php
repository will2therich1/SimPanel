<?php

namespace FtpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\SettingService;

class GameServerFtpController extends Controller
{
    /**
     * @Route("/admin/servers/g/{id}/ftp" , name="GameServerFtp")
     */
    public function indexAction(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);
        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "";
        $data['branding'] = $settingService->getSiteInformation();


        return $this->render('FtpBundle/GameServers/view.game.server.files.html.twig', $data);
    }



}
