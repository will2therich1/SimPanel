<?php

namespace ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use ServerBundle\Entity\defaultConfiguration;

class TemplateDefaultsController extends Controller
{

    /**
     * @Route("/settings/server/templates/defaults/g", name="ServerTemplateDefaults")
     */
    public function indexAction(Request $request)
    {

        $data = [];
        $data['branding'] = $this->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['serverId'] = '';
        $data['tab'] = '';
        $serverId = $request->get('ServerToEdit');

        if ($serverId !== null && $serverId != "") {
            return new RedirectResponse("/settings/server/templates/defaults/g/$serverId/general");
        }


        $em = $this->getDoctrine()->getManager();
        $templates = $em->getRepository("AppBundle:ServerTemplate")->findAll();


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "ServerDefaults";
        $data['templates'] = $templates;

        return $this->render('/serverBundle/settings/server.default.settings.select.html.twig', $data);
    }

    /**
     * @Route("/settings/server/templates/defaults/g/{id}/general", name="ServerTemplateDefaultsID")
     */
    public function templateEditor(Request $request)
    {
        // Get the entity manager!!
        $em = $this->getDoctrine()->getManager();

        $data = [];
        $data['branding'] = $this->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        $data['tab'] = 'General';
        $serverId = $request->get('id');
        $data['serverId'] = $serverId;

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
     * Gets the site information and returns this
     *
     * @return array
     */
    public function getSiteInformation()
    {

        $returnArray = [];
        $returnArray['panelName'] = $this->getSetting('PanelName')->getSettingValue();
        $returnArray['panelNamePart1'] = $this->getSetting('PanelNamePart1')->getSettingValue();
        $returnArray['PanelNamePart2'] = $this->getSetting('PanelNamePart2')->getSettingValue();
        $returnArray['PanelNameShortPart1'] = $this->getSetting('PanelNameShortPart1')->getSettingValue();
        $returnArray['PanelNameShortPart2'] = $this->getSetting('PanelNameShortPart2')->getSettingValue();

        return $returnArray;
    }

    /**
     * Returns the Setting Object!
     *
     * If the setting dosen't exist then it will be created.
     *
     * @param $settingName
     *          Name of the Setting
     * @return Settings|mixed
     */
    public function getSetting($settingName)
    {
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings');
        $query = $settings->createQueryBuilder('s');
        $result = $query->select('s.id')
            ->where('s.settingName = :setting')
            ->setParameter('setting', $settingName)
            ->getQuery()
            ->execute();

        if (empty($result)) {
            $newSetting = new Settings();
            $newSetting->setSettingName($settingName);
            $newSetting->setSettingValue(0);
            $newSetting->setSettingUpdatedTime(new \DateTime());

            $this->getDoctrine()->getManager()->persist($newSetting);
            $this->getDoctrine()->getManager()->flush();

            return $newSetting;
        }

        $result = $result[0];
        $id = $result['id'];


        $returnObject = $this->getDoctrine()->getRepository('AppBundle:Settings')->find($id);

        return $returnObject;
    }

}