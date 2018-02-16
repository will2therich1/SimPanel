<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\SettingService;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\NetworkServerService;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use UserBundle\Service\PermissionsService;
use ServerBundle\Service\GameServerService;

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


        if($user->getSubUser() === 1)
        {
            $permissionsService = new PermissionsService($user, $em);

            $permission = $permissionsService->checkPermission("USER_VIEW_SERVER");
            $owner = $permissionsService->getSubUserOwner();

            if ($permission) {
                $userId = $owner->getId();
            }
        }

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
        $data = [];



        $serverId = $request->get('id');

        $user = $this->getUser();
        $userId = $user->getId();


        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $em = $this->getDoctrine()->getManager();


        if($user->getSubUser() === 1)
        {
            $permissionsService = new PermissionsService($user, $em);

            $permission = $permissionsService->checkPermission("USER_VIEW_SERVER");

            $editable = $permissionsService->checkPermission("USER_EDIT_SERVER");

            $canManage = $permissionsService->checkPermission("USER_MANAGE_SERVER");

            if ($permission) {

            }else{
                return new RedirectResponse('/user');
            }
        } else
        {
            // If not a sub user set these variables.
            $canManage = null;
            $editable = null;
        }


        $server = $em->getRepository('ServerBundle:GameServer')->find($serverId);

        if ($userId !== $server->getOwnerId() && $user->getSubUser() == 0)
        {
            return new RedirectResponse('/user');
        } elseif ($user->getSubUser() == 1 && $user->getSubUserFor() !== $server->getOwnerId())
        {
            return new RedirectResponse('/user');

        }

        if ($server->getStatus() === "Starting" || $server->getStatus() === "Online")
        {
            $gameServerService =  new GameServerService();

            try {
                $serverQuery = $gameServerService->queryServer($server->getIp(), $server->getPort(), $server->getQueryEngine());

                if ($serverQuery[$server->getIp().":".$server->getPort()]['gq_online'] == 1){
                    $server->setStatus("Online");
                    $em->persist($server);
                    $em->flush();
                }
            } catch (\Exception $e){
                $serverQuery = "Failed with message" . $e->getMessage();
            }

        }



        if ($request->getMethod() == "POST")
        {
            if ($user->getSubUser() == 1 && $editable){
                $server->setStartupExtra($request->get('startupParams'));
            }elseif($user->getSubUser() == 1 && !$editable){
                $data['error'] = "You do not have permission to edit this server";
            }else{
                $server->setStartupExtra($request->get('startupParams'));
            }

            $em->persist($server);
            $em->flush();

        }


        $data['user'] = $user->getUserInfo();
        $data['active'] = "gameServer";
        $data['server'] = $server;
        $data['subUser'] = $user->getSubUser();
        $data['manage'] = $canManage;
        $data['site'] = $settingService->getSiteInformation();

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

    /**
     * @Route("/user/servers/g/{id}/manage" , name="userServerMainControl")
     */
    public function serverMainControl(Request $request)
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
        // Get the setting Service
        $settingService = new SettingService($em);

        $userId = $user->getId();
        $ownerId = $server->getOwnerId();

        if ($userId === $ownerId ) {


            if ($request->getMethod() == "POST") {
                $action = $request->get('do');

                if ($action == "start") {
                    // Get the start command.
                    $startCommandRaw = $server->getStartupCommand();
                    $processedStartCommand = $server->formatStartCMD($startCommandRaw, $server, $template);

                    // Now we need the server service
                    $networkServerService = new NetworkServerService($encryptionService, $networkServer, $em);

                    // Start/Restart the server.
                    $networkServerService->restartServer($processedStartCommand, $server, $user, $em);


                } elseif ($action == "stop") {

                    // we need the server service
                    $networkServerService = new NetworkServerService($encryptionService, $networkServer, $em);

                    // Stop the server.
                    $networkServerService->stopServer($server, $user, $em);


                } elseif ($action == "restart") {

                } elseif ($action == "reinstall") {

                    // Make the network server service
                    $networkServerService = new NetworkServerService($encryptionService, $networkServer, $em);

                    // Get the server Id
                    $serverId = $server->getId();
                    // Make the callback URL
                    $callbackUrl = $request->getHttpHost() . "/cron/serverCallback/$serverId";
                    // Reinstall the server
                    $networkServerService->reinstallServer($server, $template, $callbackUrl, $user);

                } elseif ($action == "delete") {

                    // Delete the server.
                    $networkServerService = new NetworkServerService($encryptionService, $networkServer, $em);
                    $networkServerService->deleteServer($user, $server);

                    // Remove from the DB
                    $em->remove($server);
                    $em->flush();

                } else {
                    return new RedirectResponse('/user');
                }

            }
        } else {
            throw new UnauthorizedHttpException('Not Allowed');
        }

        $data['currentUser'] = $user->getUserInfo();
        $data['active'] = "GameServers";
        $data['server'] = $server;
        $data['owner'] = $user;
        $data['branding'] = $settingService->getSiteInformation();


        $serverId = $server->getId();
        return new RedirectResponse("/admin/servers/g/$serverId");


    }





}
