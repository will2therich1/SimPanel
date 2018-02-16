<?php

namespace AppBundle\Controller;

use AppBundle\Service\EncryptionService;
use AppBundle\Service\ServerUserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\NetworkServer;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\NetworkServerService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\SettingService;

class NetworkServerController extends Controller
{
    /**
     * @Route("/admin/network/create", name="CreateNetworkServer")
     */
    public function createNetworkServerPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['active'] = 'Network';
        $data['success'] = '';
        $data['error'] = '';


        if (isset($_POST['name'])) {
            $serverName = $_POST['name'];
            $loginUser = $_POST['login_name'];
            $ftpPort = $_POST['ftp_port'];
            $serverIp = $_POST['ip'];
            $sshPrivateKey = $_POST['ssh_private_key'];
            $sshPrivateKeyPassword = $_POST['ssh_private_key_password'];


            if ($data['error'] == '') {
                $server = new NetworkServer();

                $server->setName($serverName);
                $server->setIp($serverIp);
                $server->setLoginUser($loginUser);
                $server->setPort($ftpPort);
                $server->setConnectionStatus('Testing');
                $server->setSshKey($sshPrivateKey, $this->getEncryptionService());
                $server->setSshPassword($sshPrivateKeyPassword, $this->getEncryptionService());

                try {
                    $em->persist($server);
                    $em->flush();
                    $data['success'] = "Server Created we will test the connection automatically!";
                } catch (\Exception $e) {
                    $data['error'] = "An unknown error occoured, please Check the provided information";
                    return $this->render('admin/network/create.network.admin.html.twig', $data);

                }
            }


        } else {

        }


        // replace this example code with whatever you need
        return $this->render('admin/network/create.network.admin.html.twig', $data);
    }


    /**
     * @Route("/admin/network/{id}", name="ViewNetworkServer")
     */
    public function viewNetworkServerPage(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);        $em = $this->getDoctrine()->getManager();

        $server = $em->getRepository('AppBundle:NetworkServer')->find($request->attributes->get('id'));
        $serverArray = array($server);

        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['active'] = 'Network';
        $data['success'] = '';
        $data['error'] = '';
        $data['server'] = $serverArray[0];


        if($request->getMethod() == "POST")
        {
            $server ->setName($request->get('name'));
            $server ->setLoginUser($request->get('login_name'));
            $server ->setPort($request->get('ftp_port'));
            $server ->setIp($request->get('ip'));
            $em->persist($server);
            $em->flush();
        }

        // replace this example code with whatever you need
        return $this->render('admin/network/view.network.admin.html.twig', $data);
    }

    /**
     * @Route("/admin/network/{id}/connectiontest", name="NetworkServerConnectionTest")
     */
    public function networkConnectiontest(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['active'] = 'Network';
        $data['success'] = '';
        $data['error'] = '';

        $server = $em->getRepository('AppBundle:NetworkServer')->find($request->attributes->get('id'));

        $networkManager = new NetworkServerService($this->getEncryptionService(), $server, $em);
        $connectionStatus = $networkManager->connectionTest();

        error_log(print_r($connectionStatus));
        if ($connectionStatus === true) {
            $server->setConnectionStatus("Connected!");
            $em->persist($server);
            $em->flush();
        } else {
            $server->setConnectionStatus("Connection Failed!");
            $em->persist($server);
            $em->flush();
        }



        // replace this example code with whatever you need
        return new RedirectResponse('/admin/network');
    }

    /**
     * @return EncryptionService
     */
    public function getEncryptionService()
    {
        $encryption_params = $this->container->getParameter('encryption');
        return new EncryptionService($encryption_params);
    }



}
