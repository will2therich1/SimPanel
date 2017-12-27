<?php

namespace AppBundle\Controller;

use AppBundle\Service\EncryptionService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\ServerTemplate;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\NetworkServerService;

class ServerTemplateController extends Controller
{

    /**
     * @Route("/admin/servers/templates/create", name="CreateServerTemplate")
     */
    public function createServerTemplatesPage(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $this->getSiteInformation();
        $data['networkServers'] = $this->getDoctrine()->getRepository('AppBundle:NetworkServer')->findAll();
        $data['active'] = 'Templates';
        $data['success'] = '';
        $data['error'] = '';

        if (null !== $request->get('name') && $request->get('name') !== '')
        {
            $template = new ServerTemplate();
            $template->setConfigId(0);
            $template->setDateCreated(time());
            $template->setLocation('/usr/local/sp/templates');
            $template->setNetworkId($request->get('networkSelect'));
            $template->setSize('Unknown');
            $template->setStatus('New');
            $template->setSteamName($request->get('steamCmdId'));
            $template->setSteamPercentage(0);
            $template->setTemplateName($request->get('name'));
            $template->setDescription($request->get('template_description'));

            try {
                $em->persist($template);
                $em->flush();
                $data['success'] = "Template has been created sending to server";

                $networkService = new NetworkServerService($this->getEncryptionService() , $em->getRepository('AppBundle:NetworkServer')->find($request->get('networkSelect')) , $em);

                $networkService->createTemplate($template->getId() , $template->getSteamName() , $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId()));

                dump($template);


            }catch (\Exception $e)
            {
                $data['error'] = "Failed with the following message:" . $e->getMessage();
            }
        }


        // replace this example code with whatever you need
        return $this->render('admin/templates/create.server.template.admin.html.twig', $data);
    }

    /**
     * @Route("/admin/servers/templates/{id}", name="ViewServerTemplate")
     */
    public function viewServerTemplatesPage(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();

        $id = $request->get('id');

        $template = $em->getRepository('AppBundle:ServerTemplate')->find($id);
        $networkServer = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());

        // Create our Data Array
        $data = [];
        $data['serverTemplate']['data'] = $template;
        $data['serverTemplate']['server'] = $networkServer;
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $this->getSiteInformation();
        $data['active'] = 'Templates';
        $data['success'] = '';
        $data['error'] = '';

        if ($request->getMethod() == 'POST')
        {
            if (null !== $request->get('deleteServer'))
            {
                // TODO: Implement deleteing server templates.
                dump($request->get('deleteServer'));

                $networkService = new NetworkServerService($this->getEncryptionService() , $networkServer , $em);
                $networkService->deleteTemplate($template);

                return new RedirectResponse('/admin/servers/templates');


            }elseif( null !== $request->get('name'))
            {
                dump("Test");
                $template->setTemplateName($request->get('name'));
                $template->setDescription($request->get('template_description'));
                $data['success'] = "Server Template Updated";
            }

        }

        $em->persist($template);
        $em->flush();

        dump($data);



        // replace this example code with whatever you need
        return $this->render('admin/templates/view.server.template.admin.html.twig', $data);
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

    /**
     * Brings you back the Encryption Service
     *
     * @return EncryptionService
     */
    public function getEncryptionService()
    {
        $encryption_params = $this->container->getParameter('encryption');
        $encryptionService = new EncryptionService($encryption_params);

        return $encryptionService;
    }

}
