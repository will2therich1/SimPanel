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

class AdminGameServerController extends Controller
{
    /**
     * @Route("/admin/servers/g/create", name="CreateGameServer")
     */
    public function indexAction(Request $request)
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
        $users = $em->getRepository("AppBundle:User")->findAll();

        $requestType = $request->getMethod();

        if ($requestType == 'POST')
        {

            $action = $request->get('action');

            if ($action == 'defaultSettings')
            {
                dump($action);

                $configId = $request->get('serverDefault');
                $user = $request->get('user');
                dump($configId);
                dump($user);

                return new RedirectResponse("/admin/servers/g/create/defaults/$configId/$user");

            }else if ($action == 'templateCreation')
            {
                dump($action);

                $templateId = $request->get('template');
                $user = $request->get('user');

                dump($templateId);
                dump($user);

            }else
            {
                dump($action);
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

        // Persist the server and flush to update the DB.
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
        $networkServerService->serverCreation($user , $networkServer , $template , $callbackUrl ,);

        // Throw them back to the create a server page.
        return new RedirectResponse("/admin/servers/g/create");
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

        if(!fsockopen($ip,$port))
        {
            $port = rand('49152' , '655325');
        }

        return $port;
    }




}
