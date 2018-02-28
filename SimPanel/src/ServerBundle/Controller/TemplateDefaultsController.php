<?php

namespace ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use ServerBundle\Entity\defaultConfiguration;
use AppBundle\Service\SettingService;

class TemplateDefaultsController extends Controller
{

    /**
     * @Route("/admin/settings/server/templates/defaults/g", name="ServerTemplateDefaults")
     */
    public function indexAction(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['serverId'] = '';
        $data['tab'] = '';
        $serverId = $request->get('ServerToEdit');

        if ($serverId !== null && $serverId != "") {
            return new RedirectResponse("/admin/settings/server/templates/defaults/g/$serverId/general");
        }


        $templates = $em->getRepository("AppBundle:ServerTemplate")->findAll();


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "ServerDefaults";
        $data['templates'] = $templates;

        return $this->render('/serverBundle/settings/server.default.settings.select.html.twig', $data);
    }

    /**
     * @Route("/admin/settings/server/templates/defaults/g/{id}/general", name="ServerTemplateDefaultsID")
     */
    public function templateEditorGeneral(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        // Build out data array
        $data = [];
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['tab'] = 'General';

        // Get our server ID from the uri
        $serverId = $request->get('id');
        $data['serverId'] = $serverId;

        // Get our server template Item
        $server = $em->getRepository('AppBundle:ServerTemplate')->find($serverId);


        // See if we already have a set of defaults.
        $cfgId = $server->getConfigId();
        if($cfgId == 0 )
        {
            $cfg = new defaultConfiguration();
            $cfg->setDefaultServerName("example");
            $cfg->setGameName("example");
            $cfg->setQueryEngine("example");
            $cfg->setStartupCommand("example");
            $cfg->setTemplateId($serverId);
            $cfg->setUpdateCommand("example");
            $cfg->setDefaultRam("1024");
            $cfg->setDefaultPlayerSlots("10");

            // Persist and flush the objects
            $em->persist($cfg);
            $em->flush();
            $server->setConfigId($cfg->getId());
            $em->persist($server);
            $em->flush();


            dump($cfg);

            $data['cfg'] = $cfg;

        }else{
            $data['cfg'] = $em->getRepository('ServerBundle:defaultConfiguration')->find($cfgId);
        }


        $post = $request->getMethod();

        // If we have a post request then we will update the config
        if ($post == "POST")
        {
            $cfg = $em->getRepository('ServerBundle:defaultConfiguration')->find($cfgId);

            $cfg->setDefaultServerName($request->get('defaultName'));
            $cfg->setGameName($request->get('nameOfGame'));

            $em->persist($cfg);
            $em->flush();

        }



        // Create our Data Array
        $data['template'] = $server;
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "ServerDefaults";

        return $this->render('/serverBundle/settings/server.default.settings.general.html.twig', $data);
    }

    /**
     * @Route("/admin/settings/server/templates/defaults/g/{id}/advanced", name="ServerTemplateDefaultsIDAdvanced")
     */
    public function templateEditorAdvanced(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        // Build out data array
        $data = [];
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['tab'] = 'Advanced';

        // Get our server ID from the uri
        $serverId = $request->get('id');
        $data['serverId'] = $serverId;

        // Get our server template Item
        $server = $em->getRepository('AppBundle:ServerTemplate')->find($serverId);


        // See if we already have a set of defaults.
        $cfgId = $server->getConfigId();
        $data['cfg'] = $em->getRepository('ServerBundle:defaultConfiguration')->find($cfgId);


        $post = $request->getMethod();

        // If we have a post request then we will update the config
        if ($post == "POST")
        {
            $cfg = $em->getRepository('ServerBundle:defaultConfiguration')->find($cfgId);

            $cfg->setDefaultRam($request->get('defaultRam'));
            $cfg->setDefaultPlayerSlots($request->get('defaultPlayerSlots'));
            $cfg->setUpdateCommand($request->get('updateCommand'));
            $cfg->setQueryEngine($request->get('queryEngine'));

            $em->persist($cfg);
            $em->flush();

        }



        // Create our Data Array
        $data['template'] = $server;
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "ServerDefaults";

        return $this->render('/serverBundle/settings/server.default.settings.advanced.html.twig', $data);
    }

    /**
     * @Route("/admin/settings/server/templates/defaults/g/{id}/startup", name="ServerTemplateDefaultsIDStartup")
     */
    public function templateEditorStartup(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        // Build out data array
        $data = [];
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['tab'] = 'Startup';

        // Get our server ID from the uri
        $serverId = $request->get('id');
        $data['serverId'] = $serverId;

        // Get our server template Item
        $server = $em->getRepository('AppBundle:ServerTemplate')->find($serverId);


        // See if we already have a set of defaults.
        $cfgId = $server->getConfigId();
        $data['cfg'] = $em->getRepository('ServerBundle:defaultConfiguration')->find($cfgId);


        $post = $request->getMethod();

        // If we have a post request then we will update the config
        if ($post == "POST")
        {
            $cfg = $em->getRepository('ServerBundle:defaultConfiguration')->find($cfgId);

            $cfg->setStartupCommand($request->get('startupCommand'));


            $em->persist($cfg);
            $em->flush();

        }



        // Create our Data Array
        $data['template'] = $server;
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "ServerDefaults";

        return $this->render('/serverBundle/settings/server.default.settings.startup.html.twig', $data);
    }



}