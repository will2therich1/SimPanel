<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\NetworkServerService;
use AppBundle\Service\ServerUserService;
use AppBundle\Service\EncryptionService;
use AppBundle\Entity\User;

class TemplateCronControllerController extends Controller
{
    /**
     * Webcron for template management
     */

    /**
     * @Route("/cron", name="cronMaster")
     */
    function cronTest()
    {
        $message = "Cron test";
        $response = new Response();
        $response->setContent($message);

        $this->steamInstall();

        return $response;

    }

    function steamInstall()
    {
        $this_page = "/admin";
        $steam_name = "740";
        $tpl_id = "2";
        $networkServer = "6";
        $cfg_steam_user = "servers4all";
        $cfg_steam_pass = "Servers4all16!";
        $cfg_steam_auth = "";

        $encryptionService = $this->getEncryptionService();
        $networkServer = $this->getDoctrine()->getRepository('AppBundle:NetworkServer')->find($networkServer);

        $serverService = new NetworkServerService($encryptionService ,$networkServer);



        $cmd  = "SteamCMDInstall -g '$steam_name' -i $tpl_id -l '$cfg_steam_user' -p '$cfg_steam_pass' -c '$cfg_steam_auth' -u '$this_page' >> /dev/null 2>&1 &";

        $serverService->runCMD($cmd);
    }

    /**
     * @return EncryptionService
     */
    public function getEncryptionService()
    {
        $encryption_params = $this->container->getParameter('encryption');
        return new EncryptionService($encryption_params);
    }
}

