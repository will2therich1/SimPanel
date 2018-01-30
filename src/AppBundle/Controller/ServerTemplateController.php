<?php

namespace AppBundle\Controller;

use AppBundle\Service\EncryptionService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\ServerTemplate;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\NetworkServerService;
use AppBundle\Service\SettingService;

class ServerTemplateController extends Controller
{

    /**
     * @Route("/admin/servers/templates/create", name="CreateServerTemplate")
     */
    public function createServerTemplatesPage(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['networkServers'] = $em->getRepository('AppBundle:NetworkServer')->findAll();
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

                $httpHost = $request->getHttpHost();
                $templateId = $template->getId();
                $callbackUrl = "$httpHost/cron/templateCallback/$templateId";

                $networkService->createTemplate($template->getId() , $template->getSteamName() , $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId()) , $callbackUrl);



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
        // Get our setting service
        $settingService = new SettingService($em);

        $id = $request->get('id');

        $template = $em->getRepository('AppBundle:ServerTemplate')->find($id);
        $networkServer = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());

        // Create our Data Array
        $data = [];
        $data['serverTemplate']['data'] = $template;
        $data['serverTemplate']['server'] = $networkServer;
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['active'] = 'Templates';
        $data['success'] = '';
        $data['error'] = '';

        if ($request->getMethod() == 'POST')
        {
            if (null !== $request->get('deleteServer'))
            {

                $networkService = new NetworkServerService($this->getEncryptionService() , $networkServer , $em);
                $networkService->deleteTemplate($template);

                return new RedirectResponse('/admin/servers/templates');


            }elseif( null !== $request->get('name'))
            {
                $template->setTemplateName($request->get('name'));
                $template->setDescription($request->get('template_description'));
                $data['success'] = "Server Template Updated";
            }

        }

        $em->persist($template);
        $em->flush();




        // replace this example code with whatever you need
        return $this->render('admin/templates/view.server.template.admin.html.twig', $data);
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
