<?php

namespace ServerBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use ServerBundle\Entity\defaultConfiguration;
use AppBundle\Service\SettingService;
use ServerBundle\Entity\GameServer;
use AppBundle\Service\NetworkServerService;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\ServerUserService;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class AdminGameServerController extends Controller
{
    /**
     * @Route("/admin/servers/g/create", name="CreateGameServer")
     */
    public function createGameServer(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get Settings Service
        $settingsService = new SettingService($em);

        $data = [];
        $data['branding'] = $settingsService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['serverId'] = '';
        $data['tab'] = '';
        $serverId = $request->get('ServerToEdit');

        if ($serverId !== null && $serverId != "") {
            return new RedirectResponse("/settings/server/templates/defaults/g/$serverId/general");
        }


        $templates = $em->getRepository("AppBundle:ServerTemplate")->findAll();
        $defaults = $em->getRepository("ServerBundle:defaultConfiguration")->findAll();

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('u')
            ->from('AppBundle:User', 'u')
            ->where('u.subUser != 1');

        $users = $queryBuilder->getQuery()->execute();

        $requestType = $request->getMethod();

        if ($requestType == 'POST')
        {

            $action = $request->get('action');

            if ($action == 'defaultSettings')
            {

                $configId = $request->get('serverDefault');
                $user = $request->get('user');

                return new RedirectResponse("/admin/servers/g/create/defaults/$configId/$user");

            }else if ($action == 'templateCreation')
            {

                $templateId = $request->get('template');
                $user = $request->get('user');

                return new RedirectResponse("/admin/servers/g/create/template/$templateId/$user");


            }

        }


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "GameServers";
        $data['templates'] = $templates;
        $data['defaults'] = $defaults;
        $data['users'] = $users;

        return $this->render('/serverBundle/gameServer/game.server.create.step.1.html.twig', $data);
    }

    /**
     * @Route("/admin/servers/g/create/defaults/{defaultId}/{ownerId}", name="CreateGameServerDefaults")
     */
    public function serverCreateDefaults(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();

        // Get the params from the url.
        $configId = $request->get('defaultId');
        $ownerId = $request->get('ownerId');

        // Get our objects from the database
        $config = $em->getRepository('ServerBundle:defaultConfiguration')->find($configId);
        $user = $em->getRepository('AppBundle:User')->find($ownerId);
        $template = $em->getRepository('AppBundle:ServerTemplate')->find($config->getTemplateId());
        $networkServer = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());

        if ($user->getSubUser() == 1)
        {
            throw new MethodNotAllowedException("" , "Sub Users cannot be given a server");
        }

        // Get the server IP and generate a port.
        $serverIp = $networkServer->getIp();
        $port =  $this->findOpenPort($serverIp);

        // Create the server database Object
        $server = new GameServer();
        $server->setGameName($config->getGameName());
        $server->setServerName($this->serverNameReplacement($config->getDefaultServerName() , $user , $template));
        $server->setOwnerId($ownerId);
        $server->setPlayerSlots($config->getDefaultPlayerSlots());
        $server->setRam($config->getDefaultRam());
        $server->setStartupCommand($config->getStartupCommand());
        $server->setUpdateCommand($config->getUpdateCommand());
        $server->setTemplateId($config->getTemplateId());
        $server->setQueryEngine('Not Implemented');
        $server->setIp($serverIp);
        $server->setPort($port);
        $server->setLocation('/');
        $server->setStatus("New");

        // Persist the server and flush to update the DB.
        $em->persist($server);
        $em->flush();


        $username = $user->getUsername();

        // Update the server location
        $server->setLocation("/usr/local/sp/users/$username/$serverIp.$port");
        $em->persist($server);
        $em->flush();

        // Lets work on the actual server now!
        $enc_service = $this->getEncryptionService();

        // Create the needed services
        $networkServerService = new NetworkServerService($enc_service ,$networkServer , $em);
        $serverUserService = new ServerUserService($enc_service , $networkServer , $user , $em);

        // See if the current user already has a server account.
        if ($user->getServerUser() === 0)
        {
            $serverUserService->createUser($user->getUsername());
        }

        // Get the server Id
        $serverId = $networkServer->getId();
        $callbackUrl = $request->getHttpHost() . "/cron/serverCallback/$serverId";

        // Actually create the server.
        $networkServerService->serverCreation($user , $networkServer , $template , $callbackUrl , $port);

        // Throw them back to the create a server page.
        return new RedirectResponse("/admin/servers/g/create");
    }

    /**
     * @Route("/admin/servers/g/create/template/{templateId}/{ownerId}", name="CreateGameServerTemplate")
     */
    public function serverCreateTemplates(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get Settings Service
        $settingsService = new SettingService($em);

        // Get the params from the url.
        $templateId = $request->get('templateId');
        $ownerId = $request->get('ownerId');

        // Get our objects from the database
        $user = $em->getRepository('AppBundle:User')->find($ownerId);
        $template = $em->getRepository('AppBundle:ServerTemplate')->find($templateId);
        $networkServer = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());

        // Get the server IP and generate a port.
        $serverIp = $networkServer->getIp();
        $port =  $this->findOpenPort($serverIp);

        if ($user->getSubUser() == 1)
        {
            throw new MethodNotAllowedException("" , "Sub Users cannot be given a server");
        }

        if ($request->getMethod() == "POST") {


            // Create the server database Object
            $server = new GameServer();
            $server->setGameName($request->get('nameOfGame'));
            $server->setServerName($this->serverNameReplacement($request->get('serverName') , $user , $template));
            $server->setOwnerId($ownerId);
            $server->setPlayerSlots($request->get('playerSlots'));
            $server->setRam($request->get('ram'));
            $server->setStartupCommand($request->get('startCommand'));
            $server->setUpdateCommand($request->get('serverName'));
            $server->setTemplateId($templateId);
            $server->setQueryEngine('Not Implemented');
            $server->setIp($serverIp);
            $server->setPort($port);
            $server->setStatus("New");
            $server->setLocation("/unknown");
            //Persist the server and flush to update the DB.
            $em->persist($server);
            $em->flush();

            $username = $user->getUsername();

            $server->setLocation("/usr/local/sp/users/$username/$serverIp.$port");
            $em->persist($server);
            $em->flush();


            // Lets work on the actual server now!
            $enc_service = $this->getEncryptionService();

            // Create the needed services
            $networkServerService = new NetworkServerService($enc_service ,$networkServer , $em);
            $serverUserService = new ServerUserService($enc_service , $networkServer , $user , $em);

            // See if the current user already has a server account.
            if ($user->getServerUser() === 0 || $user->getServerUser() == null)
            {
                $serverUserService->createUser($user->getUsername());
            }

            // Get the server Id
            $serverId = $networkServer->getId();
            $callbackUrl = $request->getHttpHost() . "/cron/serverCallback/$serverId";

            // Actually create the server.
            $networkServerService->serverCreation($user , $networkServer , $template , $callbackUrl , $port);

            // Throw them back to the create a server page.
            return new RedirectResponse("/admin/servers/g/create");

        }

        $data = [];
        $data['branding'] = $settingsService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['serverId'] = '';
        $data['tab'] = '';
        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "GameServers";
        $data['template'] = $template;
        $data['server'] = $networkServer;


        return $this->render('/serverBundle/gameServer/game.server.create.step.2.html.twig', $data);
    }

    /**
     * @Route("/admin/servers/g/{id}", name="adminViewUserGameServer")
     */
    public function viewUserGameServer(Request $request)
    {
        $data = [];

        $serverId = $request->get('id');

        $user = $this->getUser();
        $userId = $user->getId();


        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $server = $em->getRepository('ServerBundle:GameServer')->find($serverId);

        if ($request->getMethod() == "POST")
        {
            $server->setServerName($request->get('serverName'));
            $server->setGameName($request->get('gameName'));
            $server->setPort($request->get('serverPort'));
            $server->setStartupCommand($request->get('startCommand'));
            $server->setUpdateCommand($request->get('updateCommand'));
            $server->setPlayerSlots($request->get('serverPlayerSlots'));
            $server->setRam($request->get('serverRam'));

            $em->persist($server);
            $em->flush();

            $data['success'] = "Server Updated";
        }



        $data['currentUser'] = $user->getUserInfo();
        $data['active'] = "GameServers";
        $data['server'] = $server;
        $data['branding'] = $settingService->getSiteInformation();
        $data['owner'] = $em->getRepository('AppBundle:User')->find($server->getOwnerId());
        // replace this example code with whatever you need
        dump($data);

        return $this->render('/serverBundle/gameServer/view.game.server.admin.html.twig', $data);
    }

    /**
     * In the server name there are these possible replacements:
     *
     * {user.name} - The user who owns the servers name
     * {user.id} - The id of the user who owns the server
     * {template.name} - The name of the server template
     *
     * This function will turn these into what they should be
     *
     *
     * @param $input
     * @return string
     */
    public function serverNameReplacement($input , $user , $template)
    {
        $string_username_replace = str_replace('{user.name}' , $user->getFirstName() , $input);

        $string_id_replace = str_replace('{user.id}' , $user->getId() , $string_username_replace);

        $string_template_name_replace = str_replace('{template.name}' , $template->getTemplateName() , $string_id_replace);

        return $string_template_name_replace;


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
     * Generates a random port.
     *
     * Not perfect but should be able to cover 90% of use cases.
     *
     * @param $ip
     * @return int
     */
    public function findOpenPort($ip)
    {
        $port = rand('49152' , '655325');

        return $port;
    }

    /**
     * @Route("/admin/servers/g", name="GameServerList")
     */
    public function gameServerListPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        $queryBuilder = $em->createQueryBuilder();


        $get = $_GET;

        // Set the offset for queries
        if (isset($get['offset']) && $get['offset'] !== '') {
            $offset = $get['offset'];
        } else {
            $offset = 0;
        }

        // Set the limit for queries
        if (isset($get['limit']) && $get['limit'] !== '') {
            $limit = $get['limit'];
        } else {
            $limit = 10;
        }


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== '') {

            $queryBuilder->select('u')
                ->from('ServerBundle:GameServer', 'gs')
                ->where('gs.server_name = :name')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('name', $get['name']);


            $result = $queryBuilder->getQuery()->execute();


        } elseif (isset($get['id']) && $get['id'] !== '') {

            $queryBuilder->select('gs')
                ->from('ServerBundle:GameServer', 'gs')
                ->where('gs.id = :id')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('id', $get['id']);

            $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)) {
            $queryBuilder->select('gs')
                ->from('ServerBundle:GameServer', 'gs')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }

        $returnServerData = [];
        $i = 0;

        // Attach the owner details to each server.
        foreach ($result as $server)
        {

            $serverOwner = $em->getRepository('AppBundle:User')->find($server->getOwnerId());

            $serverData = $this->seraliseServerData($server , $serverOwner);
            $returnServerData['gameServer'][$i] = $serverData;
            $i++;
        }

        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['pages'] = $this->createPagination('/admin/servers/g', $offset, $limit);
        $data['server'] = $returnServerData;

        // Fix an error if the [server] array is empty (e.g there are no gameservers).
        if ($returnServerData == null)
        {
            $data['server']['gameServer'] = [];
        }

        $data['active'] = "GameServers";


        // replace this example code with whatever you need
        return $this->render('/serverBundle/gameServer/view.game.servers.admin.html.twig', $data);
    }

    /**
     * @param $url
     *       The url for the pagination to link to.
     * @param $offset
     *       The current Offset
     * @param $limit
     *       The current Limit
     *
     * @return array
     *        Returns array containing the two necessary links
     */
    public function createPagination($url, $offset, $limit)
    {
        // Setting the limit
        $nextLimit = $limit + 10;

        if ($limit == 10) {
            $lastLimit = 10;
        } else {
            $lastLimit = $limit - 10;
        }

        // Setting the offset
        $nextOffset = $offset + 10;

        if ($offset == 0) {

            $lastOffset = 0;
        } else {

            $lastOffset = $offset - 10;
        }

        // Create the links
        $nextPageLink = "$url?limit=$nextLimit &offset=$nextOffset";
        $lastPageLink = "$url?limit=$lastLimit&offset=$lastOffset";

        $data['nextlink'] = $nextPageLink;
        $data['lastlink'] = $lastPageLink;

        return $data;
    }

    public function seraliseServerData(GameServer $server , User $user)
    {
        $data = $server->getServerInfomation();
        $data['owner'] = $user->getUserInfo();

        return $data;
    }

    /**
     * @Route("/admin/servers/g/{id}/manage" , name="serverMainControl")
     */
    public function serverMainControl(Request $request)
    {
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
        // Get the user
        $user = $em->getRepository('AppBundle:User')->find($server->getOwnerId());

        if ($request->getMethod() == "POST")
        {
            $action = $request->get('do');

            if ($action == "start")
            {
                // Get the start command.
                $startCommandRaw = $server->getStartupCommand();
                $processedStartCommand = $server->formatStartCMD($startCommandRaw , $server , $template);

                // Now we need the server service
                $networkServerService = new NetworkServerService($encryptionService , $networkServer , $em);

                // Start/Restart the server.
                $networkServerService->restartServer($processedStartCommand ,$server , $user , $em);



            }elseif ($action == "stop")
            {

                // we need the server service
                $networkServerService = new NetworkServerService($encryptionService , $networkServer , $em);

                // Stop the server.
                $networkServerService->stopServer($server , $user , $em);


            }elseif ($action == "restart")
            {

            }elseif ($action == "reinstall"){

                // Make the network server service
                $networkServerService = new NetworkServerService($encryptionService , $networkServer , $em);

                // Get the server Id
                $serverId = $server->getId();
                // Make the callback URL
                $callbackUrl = $request->getHttpHost() . "/cron/serverCallback/$serverId";
                // Reinstall the server
                $networkServerService->reinstallServer($server , $template , $callbackUrl , $user );

            }elseif ($action == "delete"){

                // Delete the server.
                $networkServerService = new NetworkServerService($encryptionService , $networkServer , $em);
                $networkServerService->deleteServer($user , $server);

                // Remove from the DB
                $em->remove($server);
                $em->flush();

            }else
            {
                return new RedirectResponse('/user');
            }

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
