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
     * @Route("/user/servers/g/{id}", name="viewUserGameServer")
     */
    public function viewUserGameServer(Request $request)
    {

        $serverId = $request->get('id');

        $user = $this->getUser();
        $userId = $user->getId();


        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $server = $em->getRepository('ServerBundle:GameServer')->find($serverId);

        $data = [];
        $data['user'] = $user->getUserInfo();
        $data['active'] = "gameServer";
        $data['server'] = $server;
        $data['site'] = $settingService->getSiteInformation();
        // replace this example code with whatever you need
        return $this->render('userBundle/gameServers/user.view.game.server.html.twig' , $data);
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




}
