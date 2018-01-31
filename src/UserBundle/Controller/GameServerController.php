<?php

namespace UserBundle\Controller;

use ServerBundle\Entity\GameServer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use AppBundle\Service\SettingService;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\NetworkServerService;
use AppBundle\Service\ServerUserService;

class GameServerController extends Controller
{

    /**
     * @Route("/user/servers/g/", name="userGameServers")
     */
    public function userIndexAction(Request $request)
    {

        $user = $this->getUser();
        $userId = $user->getId();


        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('gs')
            ->from('ServerBundle:GameServer', 'gs')
            ->where('gs.ownerId LIKE :id')
            ->setParameter('id', $userId);

        $result = $queryBuilder->getQuery()->execute();

        $data = [];
        $data['user'] = $user->getUserInfo();
        $data['active'] = "gameServer";
        $data['servers'] = $result;
        $data['site'] = $settingService->getSiteInformation();
        // replace this example code with whatever you need
        return $this->render('userBundle/gameServers/user.servers.html.twig' , $data);
    }

    /**
     * @Route("/user/servers/g/{id}/power" , name="serverPowerControl")
     */
    public function serverPowerControl(Request $request)
    {
        // Get the user
        $user = $this->getUser();
        // Get doctrine
        $em = $this->getDoctrine()->getManager();
        // Get the server
        $server = $em->getRepository('ServerBundle:GameServer')->find($request->get('id'));
        // Get the template
        $template = $em->getRepository('AppBundle:ServerTemplate')->find($server->getTemplateId());
        // Get the network server
        $networkServer = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());
        // Get the encryption service
        $encryptionService  = $this->getEncryptionService();


        if ($request->getMethod() == "POST")
        {
            $action = $request->get('do');

            if ($action == "start")
            {
                // Get the start command.
                $startCommandRaw = $server->getStartupCommand();
                $processedStartCommand = $this->formatStartCMD($startCommandRaw , $server , $template);

                // Now we need the server service
                $networkServerService = new NetworkServerService($encryptionService , $networkServer , $em);

                // Start/Restart the server.
                $networkServerService->restartServer($processedStartCommand ,$server , $user , $em);



            }elseif ($action == "stop")
            {

            }elseif ($action == "restart")
            {

            }else
            {
                return new RedirectResponse('/user');
            }

        }




    }

    /**
     * Makes a instance of the Encryption Service
     *
     * @return EncryptionService
     */
    public function getEncryptionService()
    {
        $encryption_params = $this->container->getParameter('encryption');
        return new EncryptionService($encryption_params);
    }

    /**
     * In the server Startup/Update command:
     *
     * {steam.name} - The steam name of the game being updated eg (340)
     * {server.ip} - The Servers Ip
     * {server.port} - The Servers Port
     *
     * This function will turn these into what they should be
     *
     *
     * @param $startCMD
     * @param GameServer $server
     * @param ServerTemplate $template
     *
     * @return string
     */
    public function formatStartCMD($startCMD , $server , $template)
    {
        $string_steam_name_replace = str_replace('{steam.name}' , $template->getSteamName() , $startCMD);

        $string_server_ip_replace = str_replace('{server.ip}' , $server->getIp() , $string_steam_name_replace);

        $string_template_port_replace = str_replace('{server.port}' , $server->getPort() , $string_server_ip_replace);

        return $string_template_port_replace;


    }



}
