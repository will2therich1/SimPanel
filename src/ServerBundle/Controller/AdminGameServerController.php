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
        // Get Settings Service
        $settingsService = new SettingService($em);

        $data = [];
        $data['branding'] = $settingsService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['serverId'] = '';
        $data['tab'] = '';

        $configId = $request->get('defaultId');
        $ownerId = $request->get('ownerId');

        $config = $em->getRepository('ServerBundle:defaultConfiguration')->find($configId);
        $user = $em->getRepository('AppBundle:User')->find($ownerId);
        $template = $em->getRepository('AppBundle:ServerTemplate')->find($config->getTemplateId());

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

        $em->persist($server);
        $em->flush();

        // Lets work on the actual server now!
        $server = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());

        $enc_service = $this->getEncryptionService();

        $networkServerService = new NetworkServerService($enc_service ,$server , $em);


        $serverUserService = new ServerUserService($enc_service , $server , $user , $em);

        if ($user->getServerUser() === 0)
        {
            $serverUserService->createUser($user->getUsername());
        }

        $serverId = $server->getId();
        $callbackUrl = $request->getHttpHost() . "/cron/serverCallback/$serverId";

        error_log($callbackUrl);
        $networkServerService->serverCreation($user , $server , $template , $callbackUrl);

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




}
